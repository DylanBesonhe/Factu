<?php

namespace App\Controller\Admin;

use App\Entity\Cgv;
use App\Entity\Emetteur;
use App\Entity\EmetteurCgv;
use App\Entity\EmetteurVersion;
use App\Entity\ParametreFacturation;
use App\Form\EmetteurType;
use App\Form\EmetteurVersionType;
use App\Form\ParametreFacturationType;
use App\Repository\CgvRepository;
use App\Repository\EmetteurCgvRepository;
use App\Repository\EmetteurRepository;
use App\Repository\EmetteurVersionRepository;
use App\Repository\ParametreFacturationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/emetteurs')]
class EmetteurController extends AbstractController
{
    #[Route('', name: 'app_admin_emetteurs')]
    public function index(EmetteurRepository $emetteurRepository): Response
    {
        return $this->render('admin/emetteurs/index.html.twig', [
            'emetteurs' => $emetteurRepository->findAllOrdered(),
        ]);
    }

    #[Route('/nouveau', name: 'app_admin_emetteurs_new')]
    public function new(
        Request $request,
        EmetteurRepository $emetteurRepository,
        EntityManagerInterface $em
    ): Response {
        $emetteur = new Emetteur();
        $form = $this->createForm(EmetteurType::class, $emetteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si par defaut, reset les autres
            if ($emetteur->isParDefaut()) {
                $emetteurRepository->resetParDefaut();
            }

            $em->persist($emetteur);
            $em->flush();

            $this->addFlash('success', 'L\'emetteur a ete cree. Ajoutez maintenant une version avec les informations detaillees.');

            return $this->redirectToRoute('app_admin_emetteurs_version_new', ['id' => $emetteur->getId()]);
        }

        return $this->render('admin/emetteurs/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_emetteurs_show', requirements: ['id' => '\d+'])]
    public function show(
        Emetteur $emetteur,
        EmetteurVersionRepository $versionRepository,
        EmetteurCgvRepository $cgvRepository,
        CgvRepository $cgvLibraryRepository
    ): Response {
        return $this->render('admin/emetteurs/show.html.twig', [
            'emetteur' => $emetteur,
            'versionActive' => $emetteur->getVersionActive(),
            'versions' => $versionRepository->findByEmetteur($emetteur),
            'cgvAssociations' => $cgvRepository->findByEmetteur($emetteur),
            'cgvDisponibles' => $cgvLibraryRepository->findNotAssociatedToEmetteur($emetteur->getId()),
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_emetteurs_edit', requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        Emetteur $emetteur,
        EmetteurRepository $emetteurRepository,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(EmetteurType::class, $emetteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si par defaut, reset les autres
            if ($emetteur->isParDefaut()) {
                $emetteurRepository->resetParDefaut($emetteur->getId());
            }

            $em->flush();

            $this->addFlash('success', 'L\'emetteur a ete modifie.');

            return $this->redirectToRoute('app_admin_emetteurs_show', ['id' => $emetteur->getId()]);
        }

        return $this->render('admin/emetteurs/edit.html.twig', [
            'form' => $form,
            'emetteur' => $emetteur,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_emetteurs_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(
        Request $request,
        Emetteur $emetteur,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('toggle' . $emetteur->getId(), $request->request->get('_token'))) {
            $emetteur->setActif(!$emetteur->isActif());
            $em->flush();

            $status = $emetteur->isActif() ? 'active' : 'desactive';
            $this->addFlash('success', "L'emetteur a ete $status.");
        }

        return $this->redirectToRoute('app_admin_emetteurs');
    }

    #[Route('/{id}/defaut', name: 'app_admin_emetteurs_default', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function setDefault(
        Request $request,
        Emetteur $emetteur,
        EmetteurRepository $emetteurRepository,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('default' . $emetteur->getId(), $request->request->get('_token'))) {
            $emetteurRepository->resetParDefaut();
            $emetteur->setParDefaut(true);
            $em->flush();

            $this->addFlash('success', "L'emetteur {$emetteur->getNom()} est maintenant l'emetteur par defaut.");
        }

        return $this->redirectToRoute('app_admin_emetteurs');
    }

    #[Route('/{id}/version', name: 'app_admin_emetteurs_version_new', requirements: ['id' => '\d+'])]
    public function newVersion(
        Request $request,
        Emetteur $emetteur,
        EmetteurVersionRepository $versionRepository,
        EntityManagerInterface $em
    ): Response {
        $version = new EmetteurVersion();
        $version->setEmetteur($emetteur);
        $version->setDateEffet(new \DateTime());

        // Pre-remplir avec la version active actuelle
        $currentVersion = $emetteur->getVersionActive();
        if ($currentVersion) {
            $version->setRaisonSociale($currentVersion->getRaisonSociale());
            $version->setFormeJuridique($currentVersion->getFormeJuridique());
            $version->setCapital($currentVersion->getCapital());
            $version->setAdresse($currentVersion->getAdresse());
            $version->setSiren($currentVersion->getSiren());
            $version->setTva($currentVersion->getTva());
            $version->setEmail($currentVersion->getEmail());
            $version->setTelephone($currentVersion->getTelephone());
            $version->setIban($currentVersion->getIban());
            $version->setBic($currentVersion->getBic());
            $version->setLogo($currentVersion->getLogo());
        }

        $form = $this->createForm(EmetteurVersionType::class, $version);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Clore les versions precedentes
            $versionRepository->closeVersionsBefore($emetteur, $version->getDateEffet());

            $em->persist($version);
            $em->flush();

            $this->addFlash('success', 'La nouvelle version a ete creee.');

            return $this->redirectToRoute('app_admin_emetteurs_show', ['id' => $emetteur->getId()]);
        }

        return $this->render('admin/emetteurs/version_new.html.twig', [
            'form' => $form,
            'emetteur' => $emetteur,
            'isFirstVersion' => $currentVersion === null,
        ]);
    }

    #[Route('/{id}/parametres', name: 'app_admin_emetteurs_params', requirements: ['id' => '\d+'])]
    public function params(
        Request $request,
        Emetteur $emetteur,
        ParametreFacturationRepository $paramRepository,
        EntityManagerInterface $em
    ): Response {
        $params = $paramRepository->getOrCreateForEmetteur($emetteur);
        $isNew = $params->getId() === null;

        $form = $this->createForm(ParametreFacturationType::class, $params);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $em->persist($params);
            }
            $em->flush();

            $this->addFlash('success', 'Les parametres de facturation ont ete enregistres.');

            return $this->redirectToRoute('app_admin_emetteurs_show', ['id' => $emetteur->getId()]);
        }

        return $this->render('admin/emetteurs/params.html.twig', [
            'form' => $form,
            'emetteur' => $emetteur,
            'params' => $params,
        ]);
    }

    #[Route('/{id}/cgv/ajouter', name: 'app_admin_emetteurs_cgv_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addCgv(
        Request $request,
        Emetteur $emetteur,
        CgvRepository $cgvRepository,
        EmetteurCgvRepository $emetteurCgvRepository,
        EntityManagerInterface $em
    ): Response {
        $cgvId = $request->request->get('cgv_id');
        $parDefaut = $request->request->getBoolean('par_defaut');

        if ($cgvId && $this->isCsrfTokenValid('add_cgv' . $emetteur->getId(), $request->request->get('_token'))) {
            $cgv = $cgvRepository->find($cgvId);

            if ($cgv) {
                // Verifier si l'association existe deja
                $existing = $emetteurCgvRepository->findByEmetteurAndCgv($emetteur, $cgv);

                if (!$existing) {
                    // Si par defaut, reset les autres
                    if ($parDefaut) {
                        $emetteurCgvRepository->resetParDefaut($emetteur);
                    }

                    $association = new EmetteurCgv();
                    $association->setEmetteur($emetteur);
                    $association->setCgv($cgv);
                    $association->setParDefaut($parDefaut);

                    $em->persist($association);
                    $em->flush();

                    $this->addFlash('success', 'La CGV a ete associee a l\'emetteur.');
                } else {
                    $this->addFlash('warning', 'Cette CGV est deja associee a l\'emetteur.');
                }
            }
        }

        return $this->redirectToRoute('app_admin_emetteurs_show', ['id' => $emetteur->getId()]);
    }

    #[Route('/{id}/cgv/{cgvId}/retirer', name: 'app_admin_emetteurs_cgv_remove', methods: ['POST'], requirements: ['id' => '\d+', 'cgvId' => '\d+'])]
    public function removeCgv(
        Request $request,
        Emetteur $emetteur,
        int $cgvId,
        CgvRepository $cgvRepository,
        EmetteurCgvRepository $emetteurCgvRepository,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('remove_cgv' . $emetteur->getId() . '_' . $cgvId, $request->request->get('_token'))) {
            $cgv = $cgvRepository->find($cgvId);

            if ($cgv) {
                $association = $emetteurCgvRepository->findByEmetteurAndCgv($emetteur, $cgv);

                if ($association) {
                    $em->remove($association);
                    $em->flush();

                    $this->addFlash('success', 'La CGV a ete dissociee de l\'emetteur.');
                }
            }
        }

        return $this->redirectToRoute('app_admin_emetteurs_show', ['id' => $emetteur->getId()]);
    }

    #[Route('/{id}/cgv/{cgvId}/defaut', name: 'app_admin_emetteurs_cgv_default', methods: ['POST'], requirements: ['id' => '\d+', 'cgvId' => '\d+'])]
    public function setCgvDefault(
        Request $request,
        Emetteur $emetteur,
        int $cgvId,
        CgvRepository $cgvRepository,
        EmetteurCgvRepository $emetteurCgvRepository,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('default_cgv' . $emetteur->getId() . '_' . $cgvId, $request->request->get('_token'))) {
            $cgv = $cgvRepository->find($cgvId);

            if ($cgv) {
                $association = $emetteurCgvRepository->findByEmetteurAndCgv($emetteur, $cgv);

                if ($association) {
                    $emetteurCgvRepository->resetParDefaut($emetteur);
                    $association->setParDefaut(true);
                    $em->flush();

                    $this->addFlash('success', 'Cette CGV est maintenant la CGV par defaut pour cet emetteur.');
                }
            }
        }

        return $this->redirectToRoute('app_admin_emetteurs_show', ['id' => $emetteur->getId()]);
    }
}
