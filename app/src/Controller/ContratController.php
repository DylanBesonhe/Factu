<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contrats')]
class ContratController extends AbstractController
{
    #[Route('', name: 'app_contrat_index')]
    public function index(): Response
    {
        return $this->render('contrat/index.html.twig');
    }

    #[Route('/{id}', name: 'app_contrat_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        return $this->render('contrat/show.html.twig', ['id' => $id]);
    }
}
