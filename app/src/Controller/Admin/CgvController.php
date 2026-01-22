<?php

namespace App\Controller\Admin;

use App\Entity\Cgv;
use App\Form\CgvType;
use App\Repository\CgvRepository;
use App\Repository\EmetteurCgvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/cgv')]
class CgvController extends AbstractController
{
    public function __construct(
        private string $cgvDirectory = ''
    ) {
        $this->cgvDirectory = dirname(__DIR__, 3) . '/storage/cgv';
    }

    #[Route('', name: 'app_admin_cgv')]
    public function index(CgvRepository $cgvRepository, EmetteurCgvRepository $emetteurCgvRepository): Response
    {
        $cgvList = $cgvRepository->findAllOrderedByDate();

        // Ajouter les codes emetteurs pour chaque CGV
        $cgvData = [];
        foreach ($cgvList as $cgv) {
            $emetteurCodes = $emetteurCgvRepository->findEmetteurCodesByCgv($cgv);
            $cgvData[] = [
                'cgv' => $cgv,
                'emetteurCodes' => $emetteurCodes,
            ];
        }

        return $this->render('admin/cgv/index.html.twig', [
            'cgv_data' => $cgvData,
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_cgv_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $cgv = new Cgv();
        $form = $this->createForm(CgvType::class, $cgv, ['require_file' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $form->get('fichier')->getData();

            if ($fichier) {
                $originalFilename = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$fichier->guessExtension();

                try {
                    if (!is_dir($this->cgvDirectory)) {
                        mkdir($this->cgvDirectory, 0755, true);
                    }

                    $fichier->move($this->cgvDirectory, $newFilename);

                    $cgv->setFichierChemin($newFilename);
                    $cgv->setFichierOriginal($fichier->getClientOriginalName());
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                    return $this->render('admin/cgv/new.html.twig', [
                        'form' => $form,
                    ]);
                }
            }

            $em->persist($cgv);
            $em->flush();

            $this->addFlash('success', 'Les CGV ont ete ajoutees. Vous pouvez maintenant les associer aux emetteurs.');

            return $this->redirectToRoute('app_admin_cgv');
        }

        return $this->render('admin/cgv/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_cgv_edit')]
    public function edit(
        Request $request,
        Cgv $cgv,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(CgvType::class, $cgv, ['require_file' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $form->get('fichier')->getData();

            if ($fichier) {
                $originalFilename = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$fichier->guessExtension();

                try {
                    if (!is_dir($this->cgvDirectory)) {
                        mkdir($this->cgvDirectory, 0755, true);
                    }

                    // Supprimer l'ancien fichier
                    $oldFile = $this->cgvDirectory.'/'.$cgv->getFichierChemin();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }

                    $fichier->move($this->cgvDirectory, $newFilename);

                    $cgv->setFichierChemin($newFilename);
                    $cgv->setFichierOriginal($fichier->getClientOriginalName());
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                    return $this->render('admin/cgv/edit.html.twig', [
                        'form' => $form,
                        'cgv' => $cgv,
                    ]);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Les CGV ont ete modifiees.');

            return $this->redirectToRoute('app_admin_cgv');
        }

        return $this->render('admin/cgv/edit.html.twig', [
            'form' => $form,
            'cgv' => $cgv,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_admin_cgv_delete', methods: ['POST'])]
    public function delete(Request $request, Cgv $cgv, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cgv->getId(), $request->request->get('_token'))) {
            // Supprimer le fichier
            $filePath = $this->cgvDirectory.'/'.$cgv->getFichierChemin();
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $em->remove($cgv);
            $em->flush();

            $this->addFlash('success', 'Les CGV ont ete supprimees.');
        }

        return $this->redirectToRoute('app_admin_cgv');
    }

    #[Route('/{id}/telecharger', name: 'app_admin_cgv_download')]
    public function download(Cgv $cgv): Response
    {
        $filePath = $this->cgvDirectory.'/'.$cgv->getFichierChemin();

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        return $this->file($filePath, $cgv->getFichierOriginal());
    }
}
