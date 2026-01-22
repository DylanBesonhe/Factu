<?php

namespace App\Controller\Admin;

use App\Entity\Cgv;
use App\Form\CgvType;
use App\Repository\CgvRepository;
use App\Repository\EmetteurCgvRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/cgv')]
#[IsGranted('ROLE_ADMIN')]
class CgvController extends AbstractController
{
    private const UPLOAD_DIRECTORY = 'cgv';

    public function __construct(
        private FileUploadService $fileUploadService
    ) {
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
        EntityManagerInterface $em
    ): Response {
        $cgv = new Cgv();
        $form = $this->createForm(CgvType::class, $cgv, ['require_file' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $form->get('fichier')->getData();

            if ($fichier) {
                try {
                    $uploadResult = $this->fileUploadService->upload($fichier, self::UPLOAD_DIRECTORY);
                    $cgv->setFichierChemin($uploadResult['filename']);
                    $cgv->setFichierOriginal($uploadResult['originalName']);
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
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(CgvType::class, $cgv, ['require_file' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $form->get('fichier')->getData();

            if ($fichier) {
                try {
                    // Supprimer l'ancien fichier
                    if ($cgv->getFichierChemin()) {
                        $this->fileUploadService->delete($cgv->getFichierChemin(), self::UPLOAD_DIRECTORY);
                    }

                    $uploadResult = $this->fileUploadService->upload($fichier, self::UPLOAD_DIRECTORY);
                    $cgv->setFichierChemin($uploadResult['filename']);
                    $cgv->setFichierOriginal($uploadResult['originalName']);
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
            if ($cgv->getFichierChemin()) {
                $this->fileUploadService->delete($cgv->getFichierChemin(), self::UPLOAD_DIRECTORY);
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
        $filePath = $this->fileUploadService->getFilePath($cgv->getFichierChemin(), self::UPLOAD_DIRECTORY);

        if (!$this->fileUploadService->exists($cgv->getFichierChemin(), self::UPLOAD_DIRECTORY)) {
            throw $this->createNotFoundException('Le fichier n\'existe pas.');
        }

        return $this->file($filePath, $cgv->getFichierOriginal());
    }
}
