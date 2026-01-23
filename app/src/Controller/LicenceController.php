<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/licences')]
#[IsGranted('ROLE_USER')]
class LicenceController extends AbstractController
{
    #[Route('', name: 'app_licence_index')]
    public function index(): Response
    {
        return $this->render('licence/index.html.twig');
    }

    #[Route('/import', name: 'app_licence_import')]
    public function import(): Response
    {
        return $this->render('licence/import.html.twig');
    }

    #[Route('/releves', name: 'app_licence_releves')]
    public function releves(): Response
    {
        return $this->render('licence/releves.html.twig');
    }
}
