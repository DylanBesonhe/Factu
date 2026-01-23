<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\ContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private ContratRepository $contratRepository,
    ) {
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        // Clients actifs
        $clientsActifs = $this->clientRepository->count(['actif' => true]);

        // Contrats par statut
        $contratsByStatut = $this->contratRepository->countByStatut();
        $contratsActifs = $contratsByStatut['actif'] ?? 0;

        // CA des contrats actifs (mensuel estimé)
        $stats = $this->contratRepository->getStatistiques();

        return $this->render('dashboard/index.html.twig', [
            'clientsActifs' => $clientsActifs,
            'contratsActifs' => $contratsActifs,
            'caMensuel' => $stats['caMensuel'],
            'caAnnuel' => $stats['caAnnuel'],
            'contratsARenouveler' => $stats['contratsARenouveler'],
        ]);
    }
}
