<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Contrat;
use App\Entity\Facture;
use App\Entity\LigneFacture;
use App\Form\AvoirFromFactureType;
use App\Form\AvoirLibreType;
use App\Form\FactureManuelleType;
use App\Form\FactureType;
use App\Form\LigneFactureType;
use App\Repository\ClientRepository;
use App\Repository\FactureRepository;
use App\Repository\LigneFactureRepository;
use App\Service\FacturationService;
use App\Service\FacturXService;
use App\Service\PdfFactureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/facturation')]
#[IsGranted('ROLE_USER')]
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

        // Factures envoyees (filtrees par mois si navigation)
        $envoyees = $factureRepository->findEnvoyees($moisParam ? $dateReference : null);

        // Calculer mois precedent et suivant (utiliser le 1er du mois pour eviter les problemes de jours)
        $premierDuMois = new \DateTime($dateReference->format('Y-m-01'));
        $moisPrecedent = (clone $premierDuMois)->modify('-1 month')->format('Y-m');
        $moisSuivant = (clone $premierDuMois)->modify('+1 month')->format('Y-m');
        $moisCourant = $dateReference->format('Y-m');

        return $this->render('facturation/workflow.html.twig', [
            'contratsAFacturer' => $contratsAFacturer,
            'brouillons' => $brouillons,
            'validees' => $validees,
            'envoyees' => $envoyees,
            'dateReference' => $dateReference,
            'moisCourant' => $moisCourant,
            'moisPrecedent' => $moisPrecedent,
            'moisSuivant' => $moisSuivant,
        ]);
    }

    #[Route('/manuelles', name: 'app_facturation_manuelles')]
    public function manuelles(Request $request, FactureRepository $factureRepository): Response
    {
        // Recuperer le mois selectionne (format YYYY-MM) ou utiliser le mois courant
        $moisParam = $request->query->get('mois');
        if ($moisParam && preg_match('/^\d{4}-\d{2}$/', $moisParam)) {
            $dateReference = new \DateTime($moisParam . '-01');
        } else {
            $dateReference = new \DateTime();
        }

        // Grouper les factures manuelles par statut
        $brouillons = $factureRepository->findManuelles(Facture::STATUT_BROUILLON, $dateReference);
        $validees = $factureRepository->findManuelles(Facture::STATUT_VALIDEE, $dateReference);
        $envoyees = $factureRepository->findManuelles(Facture::STATUT_ENVOYEE, $dateReference);
        $payees = $factureRepository->findManuelles(Facture::STATUT_PAYEE, $dateReference);

        // Calculer mois precedent et suivant
        $premierDuMois = new \DateTime($dateReference->format('Y-m-01'));
        $moisPrecedent = (clone $premierDuMois)->modify('-1 month')->format('Y-m');
        $moisSuivant = (clone $premierDuMois)->modify('+1 month')->format('Y-m');
        $moisCourant = $dateReference->format('Y-m');

        return $this->render('facturation/manuelles/index.html.twig', [
            'brouillons' => $brouillons,
            'validees' => $validees,
            'envoyees' => array_merge($envoyees, $payees),
            'dateReference' => $dateReference,
            'moisCourant' => $moisCourant,
            'moisPrecedent' => $moisPrecedent,
            'moisSuivant' => $moisSuivant,
        ]);
    }

    #[Route('/manuelles/new', name: 'app_facturation_manuelles_new')]
    public function manuellesNew(Request $request, ClientRepository $clientRepository): Response
    {
        $form = $this->createForm(FactureManuelleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->get('client')->getData();
            $contrat = $form->get('contrat')->getData();
            $commentaire = $form->get('commentaire')->getData();

            try {
                $facture = $this->facturationService->creerFactureManuelle($client, null, $contrat);
                if ($commentaire) {
                    $facture->setCommentaire($commentaire);
                }

                $this->addFlash('success', 'Facture creee avec succes. Ajoutez maintenant des lignes.');
                return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('facturation/manuelles/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/avoirs', name: 'app_facturation_avoirs')]
    public function avoirs(Request $request, FactureRepository $factureRepository): Response
    {
        // Recuperer le mois selectionne (format YYYY-MM) ou utiliser le mois courant
        $moisParam = $request->query->get('mois');
        if ($moisParam && preg_match('/^\d{4}-\d{2}$/', $moisParam)) {
            $dateReference = new \DateTime($moisParam . '-01');
        } else {
            $dateReference = new \DateTime();
        }

        // Grouper les avoirs par statut
        $brouillons = $factureRepository->findAvoirs(Facture::STATUT_BROUILLON, $dateReference);
        $valides = $factureRepository->findAvoirs(Facture::STATUT_VALIDEE, $dateReference);
        $envoyes = $factureRepository->findAvoirs(Facture::STATUT_ENVOYEE, $dateReference);
        $rembourses = $factureRepository->findAvoirs(Facture::STATUT_REMBOURSEE, $dateReference);

        // Calculer mois precedent et suivant
        $premierDuMois = new \DateTime($dateReference->format('Y-m-01'));
        $moisPrecedent = (clone $premierDuMois)->modify('-1 month')->format('Y-m');
        $moisSuivant = (clone $premierDuMois)->modify('+1 month')->format('Y-m');
        $moisCourant = $dateReference->format('Y-m');

        return $this->render('facturation/avoirs/index.html.twig', [
            'brouillons' => $brouillons,
            'valides' => $valides,
            'envoyes' => array_merge($envoyes, $rembourses),
            'dateReference' => $dateReference,
            'moisCourant' => $moisCourant,
            'moisPrecedent' => $moisPrecedent,
            'moisSuivant' => $moisSuivant,
        ]);
    }

    #[Route('/avoirs/new', name: 'app_facturation_avoirs_new')]
    public function avoirsNew(Request $request): Response
    {
        $form = $this->createForm(AvoirLibreType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->get('client')->getData();
            $motif = $form->get('motif')->getData();

            try {
                $avoir = $this->facturationService->creerAvoirLibre($client, null, $motif);
                $this->addFlash('success', 'Avoir cree avec succes. Ajoutez maintenant des lignes.');
                return $this->redirectToRoute('app_facturation_show', ['id' => $avoir->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('facturation/avoirs/new.html.twig', [
            'form' => $form,
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
        // Recuperer la date de reference pour la periode
        $moisParam = $request->request->get('mois');

        if (!$this->isCsrfTokenValid('creer_facture' . $contrat->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_facturation_workflow', $moisParam ? ['mois' => $moisParam] : []);
        }

        $dateReference = null;
        if ($moisParam && preg_match('/^\d{4}-\d{2}$/', $moisParam)) {
            $dateReference = new \DateTime($moisParam . '-15');
        }

        try {
            $facture = $this->facturationService->creerFacture($contrat, $dateReference);
            $this->addFlash('success', 'Facture creee avec succes');
            return $this->redirectToRoute('app_facturation_workflow', $moisParam ? ['mois' => $moisParam] : []);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la creation de la facture: ' . $e->getMessage());
            return $this->redirectToRoute('app_facturation_workflow', $moisParam ? ['mois' => $moisParam] : []);
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

    #[Route('/{id}/facturx', name: 'app_facturation_facturx', requirements: ['id' => '\d+'])]
    public function facturx(Facture $facture, FacturXService $facturxService): Response
    {
        if ($facture->getStatut() === Facture::STATUT_BROUILLON) {
            throw $this->createAccessDeniedException('Factur-X uniquement pour factures validees');
        }

        $pdfContent = $facturxService->generateFacturX($facture);
        $filename = $facturxService->getFilename($facture);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
        $redirectParams = $this->getRedirectParamsForFacture($facture);

        if (!$this->isCsrfTokenValid('valider' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
        }

        try {
            $this->facturationService->validerFacture($facture);
            $this->addFlash('success', ($facture->isAvoir() ? 'Avoir' : 'Facture') . ' valide(e) avec succes');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
    }

    #[Route('/{id}/envoyer', name: 'app_facturation_envoyer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function envoyer(Request $request, Facture $facture): Response
    {
        $redirectParams = $this->getRedirectParamsForFacture($facture);

        if (!$this->isCsrfTokenValid('envoyer' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
        }

        try {
            $this->facturationService->marquerEnvoyee($facture);
            $this->addFlash('success', ($facture->isAvoir() ? 'Avoir' : 'Facture') . ' marque(e) comme envoye(e)');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
    }

    #[Route('/{id}/payer', name: 'app_facturation_payer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function payer(Request $request, Facture $facture): Response
    {
        $redirectParams = $this->getRedirectParamsForFacture($facture);

        if (!$this->isCsrfTokenValid('payer' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
        }

        try {
            $this->facturationService->marquerPayee($facture);
            $this->addFlash('success', 'Facture marquee comme payee');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
    }

    #[Route('/{id}/avoir/new', name: 'app_facturation_avoir_new', requirements: ['id' => '\d+'])]
    public function avoirNew(Request $request, Facture $facture): Response
    {
        if ($facture->isAvoir()) {
            $this->addFlash('error', 'Impossible de creer un avoir sur un avoir');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        $statutsAutorises = [Facture::STATUT_VALIDEE, Facture::STATUT_ENVOYEE, Facture::STATUT_PAYEE];
        if (!in_array($facture->getStatut(), $statutsAutorises)) {
            $this->addFlash('error', 'Un avoir ne peut etre cree que sur une facture validee, envoyee ou payee');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        $montantDisponible = $this->facturationService->getMontantAvoirDisponible($facture);
        if (bccomp($montantDisponible, '0.00', 2) <= 0) {
            $this->addFlash('error', 'Cette facture a deja ete entierement annulee par des avoirs');
            return $this->redirectToRoute('app_facturation_show', ['id' => $facture->getId()]);
        }

        $form = $this->createForm(AvoirFromFactureType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->get('type')->getData();
            $motif = $form->get('motif')->getData();

            try {
                $total = ($type === 'total');
                $avoir = $this->facturationService->creerAvoirFromFacture($facture, $total, $motif);
                $this->addFlash('success', 'Avoir cree avec succes. ' . ($total ? '' : 'Modifiez les lignes selon vos besoins.'));
                return $this->redirectToRoute('app_facturation_show', ['id' => $avoir->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('facturation/avoirs/new_from_facture.html.twig', [
            'facture' => $facture,
            'form' => $form,
            'montantDisponible' => $montantDisponible,
        ]);
    }

    #[Route('/{id}/rembourser', name: 'app_facturation_rembourser', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function rembourser(Request $request, Facture $avoir): Response
    {
        $redirectParams = $this->getRedirectParamsForFacture($avoir);

        if (!$this->isCsrfTokenValid('rembourser' . $avoir->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
        }

        try {
            $this->facturationService->marquerRembourse($avoir);
            $this->addFlash('success', 'Avoir marque comme rembourse');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
    }

    #[Route('/{id}/supprimer', name: 'app_facturation_supprimer', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function supprimer(Request $request, Facture $facture): Response
    {
        $redirectParams = $this->getRedirectParamsForFacture($facture);

        if (!$this->isCsrfTokenValid('supprimer' . $facture->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
        }

        try {
            $numero = $facture->getNumero() ?? 'Brouillon #' . $facture->getId();
            $this->facturationService->supprimerFacture($facture);
            $this->addFlash('success', ($facture->isAvoir() ? 'Avoir' : 'Facture') . ' ' . $numero . ' supprime(e) avec succes');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute($redirectParams['route'], $redirectParams['params']);
    }

    /**
     * Determine la route et les parametres de redirection pour une facture
     * @return array{route: string, params: array}
     */
    private function getRedirectParamsForFacture(Facture $facture): array
    {
        $mois = $facture->getDateFacture()?->format('Y-m');
        $params = $mois ? ['mois' => $mois] : [];

        if ($facture->isAvoir()) {
            return ['route' => 'app_facturation_avoirs', 'params' => $params];
        }

        if ($facture->getContrat() === null) {
            return ['route' => 'app_facturation_manuelles', 'params' => $params];
        }

        return ['route' => 'app_facturation_workflow', 'params' => $params];
    }
}
