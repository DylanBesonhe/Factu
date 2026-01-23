<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/documentation')]
#[IsGranted('ROLE_USER')]
class DocumentationController extends AbstractController
{
    #[Route('', name: 'app_documentation_index')]
    public function index(): Response
    {
        return $this->render('documentation/index.html.twig');
    }
}
