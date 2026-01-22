<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/facturation')]
class FacturationController extends AbstractController
{
    #[Route('', name: 'app_facturation_workflow')]
    public function workflow(): Response
    {
        return $this->render('facturation/workflow.html.twig');
    }

    #[Route('/liste', name: 'app_facturation_liste')]
    public function liste(): Response
    {
        return $this->render('facturation/liste.html.twig');
    }
}
