<?php

namespace App\Controller\Admin;

use App\Entity\Module;
use App\Form\ModuleType;
use App\Repository\ModuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/modules')]
#[IsGranted('ROLE_ADMIN')]
class ModuleController extends AbstractController
{
    #[Route('', name: 'app_admin_modules')]
    public function index(ModuleRepository $moduleRepository): Response
    {
        $modules = $moduleRepository->findAllOrderedByName();

        return $this->render('admin/modules/index.html.twig', [
            'modules' => $modules,
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_modules_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $module = new Module();
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($module);
            $em->flush();

            $this->addFlash('success', 'Le module a ete cree.');

            return $this->redirectToRoute('app_admin_modules');
        }

        return $this->render('admin/modules/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_modules_edit')]
    public function edit(Request $request, Module $module, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le module a ete modifie.');

            return $this->redirectToRoute('app_admin_modules');
        }

        return $this->render('admin/modules/edit.html.twig', [
            'form' => $form,
            'module' => $module,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_modules_toggle', methods: ['POST'])]
    public function toggle(Request $request, Module $module, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$module->getId(), $request->request->get('_token'))) {
            $module->setActif(!$module->isActif());
            $em->flush();

            $status = $module->isActif() ? 'active' : 'desactive';
            $this->addFlash('success', "Le module a ete {$status}.");
        }

        return $this->redirectToRoute('app_admin_modules');
    }
}
