<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\Facture;
use App\Entity\LigneFacture;
use App\Form\FactureType;
use App\Form\LigneFactureType;
use App\Repository\FactureRepository;
use App\Repository\LigneFactureRepository;
use App\Service\FacturationService;
use App\Service\PdfFactureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/facturation')]
class FacturationController extends AbstractController
{
    public function __construct(
        private FacturationService $facturationService
    ) {
    }

    #[Route('', name: 'app_facturation_workflow')]
    public function workflow(Request $request, FactureRepository $factureRepository): Response
    {
        // Recuperer le mois selectionne (format YYYY-MM) ou utiliser le mois courant
        $moisParam = $request->query->get('mois');
        if ($moisParam && preg_match('/^\d{4}-\d{2}$/', $moisParam)) {
            // Utiliser le dernier jour du mois pour que tous les contrats du mois soient visibles
            $dateReference = new \DateTime($moisParam . '-01');
            $dateReference->modify('last day of this month');
        } else {
            $dateReference = new \DateTime();
        }

        // Contrats a facturer groupes par periodicite
        $contratsAFacturer = $this->facturationService->findContratsAFacturer($dateReference);

        // Brouillons (filtres par mois si navigation)
        $brouillons = $factureRepository->findBrouillons($moisParam ? $dateReference : null);

        // Factures validees (filtrees par mois si navigation)
        $validees = $factureRepository->findValidees($moisParam ? $dateReference : null);

        // Calculer mois precedent et suivant (utiliser le 1er du mois pour eviter les problemes de jours)
        $premierDuMois = new \DateTime($dateReference->format('Y-m-01'));
        $moisPrecedent = (clone $premierDuMois)->modify('-1 month')->format('Y-m');
        $moisSuivant = (clone $premierDuMois)->modify('+1 month')->format('Y-m');
        $moisCourant = $dateReference->format('Y-m');

        return $this->render('facturation/workflow.html.twig', [
            'contratsAFacturer' => $contratsAFacturer,
            'brouillons' => $brouillons,
            'validees' => $validees,
            'dateReference' => $dateReference,
            'moisCourant' => $moisCourant,
            'moisPrecedent' => $moisPrecedent,
            'moisSuivant' => $moisSuivant,
        ]);
    }

    #[Route('/liste', name: 'app_facturation_liste')]
    public function liste(Request $request, FactureRepository $factureRepository): Response
    {
        $search = $request->query->get('search');
        $statut = $request->query->get('statut');
        $sort = $request->query->get('sort', 'dateFacture');
        $direction = $request->query->get('direction', 'DESC');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;

        $factures = $factureRepository->searchWithFilters(
            $search,
            $statut,
            null,
            $sort,
            $direction,
            $page,
            $limit
        );

        $countByStatut = $factureRepository->countByStatut();

        return $this->render('facturation/liste.html.twig', [
            'factures' => $factures,
            'countByStatut' => $countByStatut,
            'search' => $search,
            'statut' => $statut,
            'sort' => $sort,
            'direction' => $direction,
            'page' => $page,
            'limit' => $limit,
            'total' => count($factures),
        ]);
    }

    #[Route('/creer/{id}', name: 'app_facturation_creer', methods: ['POST'])]
    public function creer(Request $request, Contrat $contrat): Response
    {
        if (!$this->isCsrfTokenValid('creer_facture' . $contrat->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_facturation_workflow');
        }

        // Recuperer la date de reference pour la periode
        $moisParam = $request->request->get('mois');
        $dateReference = null;
        if ($moisParam && preg_match('/^\d{4}-\d{2}$/', $moisParam)) {
            $dateReference = new \DateTime($moisParam . '-15');
        }

        try {
            $facture = $this->facturationService->creerFacture($contrat, $dateReference);
            $this->addFlash('success', 'Facture ' . $facture->getNumero() . ' creee avec succes');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la creation de la facture: ' . $e->getMessage());
            return $this->redirectToRoute('app_facturation_workflow');
        }
    }

    #[Route('/{id}', name: 'app_facturation_show', requirements: ['id' => '\d+'])]
    public function show(Facture $facture): Response
    {
        return $this->render('facturation/show.html.twig', [
            'facture' => $facture,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_facturation_pdf', requirements: ['id' => '\d+'])]
    public function pdf(Facture $facture, PdfFactureService $pdfService): Response
    {
        $pdfContent = $pdfService->generatePdf($facture);
        $filename = $pdfService->getFilename($facture);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_facturation_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        if (!$facture->isEditable()) {
            $this->addFlash('error', 'Seules les factures en brouillon peuvent etre modifiees');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->facturationService->recalculerFacture($facture);
            $this->addFlash('success', 'Facture modifiee avec succes');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        return $this->render('facturation/edit.html.twig', [
            'facture' => $facture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/ligne/new', name: 'app_facturation_ligne_new', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function addLigne(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        if (!$facture->isEditable()) {
            $this->addFlash('error', 'Seules les factures en brouillon peuvent etre modifiees');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        $ligne = new LigneFacture();
        $form = $this->createForm(LigneFactureType::class, $ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ligne->calculerTotaux();
            $facture->addLigne($ligne);
            $facture->recalculerTotaux();
            $em->persist($ligne);
            $em->flush();

            $this->addFlash('success', 'Ligne ajoutee avec succes');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        return $this->render('facturation/ligne_form.html.twig', [
            'facture' => $facture,
            'form' => $form,
            'ligne' => $ligne,
            'mode' => 'new',
        ]);
    }

    #[Route('/{id}/ligne/{ligneId}/edit', name: 'app_facturation_ligne_edit', requirements: ['id' => '\d+', 'ligneId' => '\d+'])]
    public function editLigne(Request $request, Facture $facture, int $ligneId, LigneFactureRepository $ligneRepository, EntityManagerInterface $em): Response
    {
        if (!$facture->isEditable()) {
            $this->addFlash('error', 'Seules les factures en brouillon peuvent etre modifiees');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        $ligne = $ligneRepository->find($ligneId);
        if (!$ligne || $ligne->getFacture() !== $facture) {
            throw $this->createNotFoundException('Ligne non trouvee');
        }

        $form = $this->createForm(LigneFactureType::class, $ligne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ligne->calculerTotaux();
            $facture->recalculerTotaux();
            $em->flush();

            $this->addFlash('success', 'Ligne modifiee avec succes');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        return $this->render('facturation/ligne_form.html.twig', [
            'facture' => $facture,
            'form' => $form,
            'ligne' => $ligne,
            'mode' => 'edit',
        ]);
    }

    #[Route('/{id}/ligne/{ligneId}/supprimer', name: 'app_facturation_ligne_supprimer', requirements: ['id' => '\d+', 'ligneId' => '\d+'], methods: ['POST'])]
    public function deleteLigne(Request $request, Facture $facture, int $ligneId, LigneFactureRepository $ligneRepository, EntityManagerInterface $em): Response
    {
        if (!$facture->isEditable()) {
            $this->addFlash('error', 'Seules les factures en brouillon peuvent etre modifiees');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        if (!$this->isCsrfTokenValid('supprimer_ligne' . $ligneId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        $ligne = $ligneRepository->find($ligneId);
        if (!$ligne || $ligne->getFacture() !== $facture) {
            throw $this->createNotFoundException('Ligne non trouvee');
        }

        $facture->removeLigne($ligne);
        $em->remove($ligne);
        $facture->recalculerTotaux();
        $em->flush();

        $this->addFlash('success', 'Ligne supprimee avec succes');
        return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
    }

    #[Route('/{id}/valider', name: 'app_facturation_valider', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function valider(Request $request, Facture $facture): Response
    {
        if (!$this->isCsrfTokenValid('valider' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        try {
            $this->facturationService->validerFacture($facture);
            $this->addFlash('success', 'Facture validee avec succes');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
    }

    #[Route('/{id}/envoyer', name: 'app_facturation_envoyer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function envoyer(Request $request, Facture $facture): Response
    {
        if (!$this->isCsrfTokenValid('envoyer' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        try {
            $this->facturationService->marquerEnvoyee($facture);
            $this->addFlash('success', 'Facture marquee comme envoyee');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
    }

    #[Route('/{id}/payer', name: 'app_facturation_payer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function payer(Request $request, Facture $facture): Response
    {
        if (!$this->isCsrfTokenValid('payer' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        try {
            $this->facturationService->marquerPayee($facture);
            $this->addFlash('success', 'Facture marquee comme payee');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
    }

    #[Route('/{id}/supprimer', name: 'app_facturation_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimer(Request $request, Facture $facture): Response
    {
        if (!$this->isCsrfTokenValid('supprimer' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_facturation_workflow');
        }

        try {
            $numero = $facture->getNumero();
            $this->facturationService->supprimerFacture($facture);
            $this->addFlash('success', 'Facture ' . $numero . ' supprimee avec succes');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_facturation_workflow');
    }
}
