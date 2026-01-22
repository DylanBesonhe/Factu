<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\ContratEvenement;
use App\Entity\ContratFichier;
use App\Entity\Instance;
use App\Entity\LigneContrat;
use App\Form\ContratEvenementType;
use App\Form\ContratType;
use App\Form\InstanceType;
use App\Form\LigneContratType;
use App\Repository\ContratEvenementRepository;
use App\Repository\ContratFichierRepository;
use App\Repository\ContratRepository;
use App\Repository\HistoriqueLicenceRepository;
use App\Repository\LigneContratRepository;
use App\Service\CsvExportService;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contrats')]
class ContratController extends AbstractController
{
    private const UPLOAD_DIRECTORY = 'contrats';

    public function __construct(
        private FileUploadService $fileUploadService
    ) {
    }

    #[Route('', name: 'app_contrat_index', methods: ['GET'])]
    public function index(Request $request, ContratRepository $contratRepository): Response
    {
        $search = $request->query->get('search');
        $statutFilter = $request->query->get('statut');
        $sort = $request->query->get('sort', 'numero');
        $direction = $request->query->get('direction', 'ASC');
        $page = max(1, $request->query->getInt('page', 1));

        $paginator = $contratRepository->searchWithFilters(
            $search,
            $statutFilter,
            null,
            null,
            $sort,
            $direction,
            $page
        );
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / 20);

        return $this->render('contrat/index.html.twig', [
            'contrats' => $paginator,
            'search' => $search,
            'statutFilter' => $statutFilter,
            'sort' => $sort,
            'direction' => $direction,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

    #[Route('/export', name: 'app_contrat_export', methods: ['GET'])]
    public function export(Request $request, ContratRepository $contratRepository, CsvExportService $csvExportService): Response
    {
        $search = $request->query->get('search');
        $statutFilter = $request->query->get('statut');

        $contrats = $contratRepository->findAllForExport($search, $statutFilter);

        return $csvExportService->exportContrats($contrats);
    }

    #[Route('/new', name: 'app_contrat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ContratRepository $contratRepository): Response
    {
        $contrat = new Contrat();
        $contrat->setNumero($contratRepository->getNextNumero());
        $contrat->setDateSignature(new \DateTime());
        $contrat->setDateAnniversaire(new \DateTime());

        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contrat);

            $evenement = new ContratEvenement();
            $evenement->setContrat($contrat);
            $evenement->setType(ContratEvenement::TYPE_CREATION);
            $evenement->setDescription('Creation du contrat');
            $evenement->setDateEffet($contrat->getDateSignature());
            $evenement->setAuteur($this->getUser()?->getUserIdentifier());
            $entityManager->persist($evenement);

            $entityManager->flush();

            $this->addFlash('success', 'Contrat cree avec succes.');

            return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
        }

        return $this->render('contrat/new.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(
        Contrat $contrat,
        HistoriqueLicenceRepository $historiqueLicenceRepository
    ): Response {
        $ligneForm = $this->createForm(LigneContratType::class, new LigneContrat());
        $evenementForm = $this->createForm(ContratEvenementType::class, new ContratEvenement());

        $historiqueLicences = $historiqueLicenceRepository->findLast12Months($contrat->getId());

        return $this->render('contrat/show.html.twig', [
            'contrat' => $contrat,
            'ligneForm' => $ligneForm,
            'evenementForm' => $evenementForm,
            'historiqueLicences' => $historiqueLicences,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contrat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Contrat modifie avec succes.');

            return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
        }

        return $this->render('contrat/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/lignes/new', name: 'app_contrat_ligne_new', methods: ['POST'])]
    public function addLigne(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $ligne = new LigneContrat();
        $form = $this->createForm(LigneContratType::class, $ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ligne->setContrat($contrat);
            $entityManager->persist($ligne);
            $entityManager->flush();

            $this->addFlash('success', 'Ligne ajoutee avec succes.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout de la ligne.');
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }

    #[Route('/{id}/lignes/{ligneId}/edit', name: 'app_contrat_ligne_edit', methods: ['POST'])]
    public function editLigne(
        Request $request,
        Contrat $contrat,
        int $ligneId,
        LigneContratRepository $ligneRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $ligne = $ligneRepository->find($ligneId);

        if ($ligne && $ligne->getContrat() === $contrat) {
            $form = $this->createForm(LigneContratType::class, $ligne);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();
                $this->addFlash('success', 'Ligne modifiee avec succes.');
            }
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }

    #[Route('/{id}/lignes/{ligneId}/delete', name: 'app_contrat_ligne_delete', methods: ['POST'])]
    public function deleteLigne(
        Request $request,
        Contrat $contrat,
        int $ligneId,
        LigneContratRepository $ligneRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $ligne = $ligneRepository->find($ligneId);

        if ($ligne && $ligne->getContrat() === $contrat) {
            if ($this->isCsrfTokenValid('delete_ligne' . $ligneId, $request->request->get('_token'))) {
                $entityManager->remove($ligne);
                $entityManager->flush();

                $this->addFlash('success', 'Ligne supprimee avec succes.');
            }
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }

    #[Route('/{id}/evenements/new', name: 'app_contrat_evenement_new', methods: ['POST'])]
    public function addEvenement(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $evenement = new ContratEvenement();
        $form = $this->createForm(ContratEvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evenement->setContrat($contrat);
            $evenement->setAuteur($this->getUser()?->getUserIdentifier());
            $entityManager->persist($evenement);
            $entityManager->flush();

            $this->addFlash('success', 'Evenement ajoute avec succes.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout de l\'evenement.');
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }

    #[Route('/{id}/evenements/{evenementId}/delete', name: 'app_contrat_evenement_delete', methods: ['POST'])]
    public function deleteEvenement(
        Request $request,
        Contrat $contrat,
        int $evenementId,
        ContratEvenementRepository $evenementRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $evenement = $evenementRepository->find($evenementId);

        if ($evenement && $evenement->getContrat() === $contrat) {
            if ($this->isCsrfTokenValid('delete_evenement' . $evenementId, $request->request->get('_token'))) {
                $entityManager->remove($evenement);
                $entityManager->flush();

                $this->addFlash('success', 'Evenement supprime avec succes.');
            }
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }

    #[Route('/{id}/fichiers/upload', name: 'app_contrat_fichier_upload', methods: ['POST'])]
    public function uploadFichier(
        Request $request,
        Contrat $contrat,
        EntityManagerInterface $entityManager
    ): Response {
        $file = $request->files->get('fichier');
        $description = $request->request->get('description');

        if ($file) {
            try {
                $uploadResult = $this->fileUploadService->upload($file, self::UPLOAD_DIRECTORY);

                $fichier = new ContratFichier();
                $fichier->setContrat($contrat);
                $fichier->setNomOriginal($uploadResult['originalName']);
                $fichier->setChemin(self::UPLOAD_DIRECTORY . '/' . $uploadResult['filename']);
                $fichier->setTypeMime($uploadResult['mimeType']);
                $fichier->setTaille($uploadResult['size']);
                $fichier->setDescription($description);

                $entityManager->persist($fichier);
                $entityManager->flush();

                $this->addFlash('success', 'Fichier uploade avec succes.');
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
            }
        } else {
            $this->addFlash('error', 'Aucun fichier selectionne.');
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }

    #[Route('/{id}/fichiers/{fichierId}/download', name: 'app_contrat_fichier_download', methods: ['GET'])]
    public function downloadFichier(
        Contrat $contrat,
        int $fichierId,
        ContratFichierRepository $fichierRepository
    ): Response {
        $fichier = $fichierRepository->find($fichierId);

        if (!$fichier || $fichier->getContrat() !== $contrat) {
            throw $this->createNotFoundException('Fichier non trouve');
        }

        $filename = basename($fichier->getChemin());
        $filePath = $this->fileUploadService->getFilePath($filename, self::UPLOAD_DIRECTORY);

        if (!$this->fileUploadService->exists($filename, self::UPLOAD_DIRECTORY)) {
            throw $this->createNotFoundException('Fichier non trouve sur le serveur');
        }

        return $this->file($filePath, $fichier->getNomOriginal());
    }

    #[Route('/{id}/fichiers/{fichierId}/delete', name: 'app_contrat_fichier_delete', methods: ['POST'])]
    public function deleteFichier(
        Request $request,
        Contrat $contrat,
        int $fichierId,
        ContratFichierRepository $fichierRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $fichier = $fichierRepository->find($fichierId);

        if ($fichier && $fichier->getContrat() === $contrat) {
            if ($this->isCsrfTokenValid('delete_fichier' . $fichierId, $request->request->get('_token'))) {
                $filename = basename($fichier->getChemin());
                $this->fileUploadService->delete($filename, self::UPLOAD_DIRECTORY);

                $entityManager->remove($fichier);
                $entityManager->flush();

                $this->addFlash('success', 'Fichier supprime avec succes.');
            }
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }

    #[Route('/{id}/statut', name: 'app_contrat_statut', methods: ['POST'])]
    public function changeStatut(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $newStatut = $request->request->get('statut');

        if ($this->isCsrfTokenValid('change_statut' . $contrat->getId(), $request->request->get('_token'))) {
            $oldStatut = $contrat->getStatut();
            $contrat->setStatut($newStatut);

            $evenement = new ContratEvenement();
            $evenement->setContrat($contrat);

            switch ($newStatut) {
                case Contrat::STATUT_SUSPENDU:
                    $evenement->setType(ContratEvenement::TYPE_SUSPENSION);
                    $evenement->setDescription('Contrat suspendu (etait: ' . $oldStatut . ')');
                    break;
                case Contrat::STATUT_RESILIE:
                    $evenement->setType(ContratEvenement::TYPE_RESILIATION);
                    $evenement->setDescription('Contrat resilie (etait: ' . $oldStatut . ')');
                    $contrat->setDateFin(new \DateTime());
                    break;
                case Contrat::STATUT_ACTIF:
                    $evenement->setType(ContratEvenement::TYPE_MODIFICATION);
                    $evenement->setDescription('Contrat reactive (etait: ' . $oldStatut . ')');
                    break;
            }

            $evenement->setDateEffet(new \DateTime());
            $evenement->setAuteur($this->getUser()?->getUserIdentifier());
            $entityManager->persist($evenement);

            $entityManager->flush();

            $this->addFlash('success', 'Statut du contrat modifie avec succes.');
        }

        return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()]);
    }
}
