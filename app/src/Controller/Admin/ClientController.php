<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\ClientLien;
use App\Entity\ClientNote;
use App\Entity\Contact;
use App\Form\ClientLienType;
use App\Form\ClientNoteType;
use App\Form\ClientType;
use App\Form\ContactType;
use App\Repository\ClientRepository;
use App\Repository\ContactRepository;
use App\Service\CsvExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/clients')]
#[IsGranted('ROLE_ADMIN')]
class ClientController extends AbstractController
{
    #[Route('', name: 'app_admin_clients')]
    public function index(Request $request, ClientRepository $clientRepository): Response
    {
        $search = $request->query->get('search');
        $actif = $request->query->get('actif');
        $sort = $request->query->get('sort', 'raisonSociale');
        $direction = $request->query->get('direction', 'ASC');
        $page = max(1, $request->query->getInt('page', 1));

        $actifFilter = null;
        if ($actif === '1') {
            $actifFilter = true;
        } elseif ($actif === '0') {
            $actifFilter = false;
        }

        $paginator = $clientRepository->searchWithFilters(
            $search,
            $actifFilter,
            $sort,
            $direction,
            $page
        );

        $totalPages = (int) ceil(count($paginator) / 20);

        return $this->render('admin/clients/index.html.twig', [
            'clients' => $paginator,
            'search' => $search,
            'actif' => $actif,
            'sort' => $sort,
            'direction' => $direction,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => count($paginator),
        ]);
    }

    #[Route('/export', name: 'app_admin_clients_export')]
    public function export(
        Request $request,
        ClientRepository $clientRepository,
        CsvExportService $csvExportService
    ): Response {
        $search = $request->query->get('search');
        $actif = $request->query->get('actif');

        $actifFilter = null;
        if ($actif === '1') {
            $actifFilter = true;
        } elseif ($actif === '0') {
            $actifFilter = false;
        }

        $clients = $clientRepository->findAllForExport($search, $actifFilter);

        return $csvExportService->exportClients($clients);
    }

    #[Route('/nouveau', name: 'app_admin_clients_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Le client a ete cree.');

            return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
        }

        return $this->render('admin/clients/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_clients_show', requirements: ['id' => '\d+'])]
    public function show(Client $client, ClientRepository $clientRepository): Response
    {
        $contactForm = $this->createForm(ContactType::class, new Contact());
        $noteForm = $this->createForm(ClientNoteType::class, new ClientNote());
        $lienForm = $this->createForm(ClientLienType::class, new ClientLien(), [
            'current_client' => $client,
        ]);

        return $this->render('admin/clients/show.html.twig', [
            'client' => $client,
            'contactForm' => $contactForm,
            'noteForm' => $noteForm,
            'lienForm' => $lienForm,
            'clientsDisponibles' => $clientRepository->findAllExcept($client),
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_admin_clients_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le client a ete modifie.');

            return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
        }

        return $this->render('admin/clients/edit.html.twig', [
            'form' => $form,
            'client' => $client,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_clients_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(Request $request, Client $client, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle' . $client->getId(), $request->request->get('_token'))) {
            $client->setActif(!$client->isActif());
            $em->flush();

            $status = $client->isActif() ? 'active' : 'desactive';
            $this->addFlash('success', "Le client a ete $status.");
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/contacts/nouveau', name: 'app_admin_clients_contact_new', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addContact(
        Request $request,
        Client $client,
        ContactRepository $contactRepository,
        EntityManagerInterface $em
    ): Response {
        $contact = new Contact();
        $contact->setClient($client);

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si principal, reset les autres
            if ($contact->isPrincipal()) {
                $contactRepository->resetPrincipal($client);
            }

            $em->persist($contact);
            $em->flush();

            $this->addFlash('success', 'Le contact a ete ajoute.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout du contact.');
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/contacts/{contactId}/supprimer', name: 'app_admin_clients_contact_delete', methods: ['POST'], requirements: ['id' => '\d+', 'contactId' => '\d+'])]
    public function deleteContact(
        Request $request,
        Client $client,
        int $contactId,
        ContactRepository $contactRepository,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete_contact' . $contactId, $request->request->get('_token'))) {
            $contact = $contactRepository->find($contactId);

            if ($contact && $contact->getClient() === $client) {
                $em->remove($contact);
                $em->flush();

                $this->addFlash('success', 'Le contact a ete supprime.');
            }
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/contacts/{contactId}/principal', name: 'app_admin_clients_contact_principal', methods: ['POST'], requirements: ['id' => '\d+', 'contactId' => '\d+'])]
    public function setPrincipal(
        Request $request,
        Client $client,
        int $contactId,
        ContactRepository $contactRepository,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('principal_contact' . $contactId, $request->request->get('_token'))) {
            $contact = $contactRepository->find($contactId);

            if ($contact && $contact->getClient() === $client) {
                $contactRepository->resetPrincipal($client);
                $contact->setPrincipal(true);
                $em->flush();

                $this->addFlash('success', 'Le contact a ete defini comme principal.');
            }
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/notes/nouveau', name: 'app_admin_clients_note_new', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addNote(Request $request, Client $client, EntityManagerInterface $em): Response
    {
        $note = new ClientNote();
        $note->setClient($client);

        $form = $this->createForm(ClientNoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $em->flush();

            $this->addFlash('success', 'La note a ete ajoutee.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout de la note.');
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/notes/{noteId}/supprimer', name: 'app_admin_clients_note_delete', methods: ['POST'], requirements: ['id' => '\d+', 'noteId' => '\d+'])]
    public function deleteNote(
        Request $request,
        Client $client,
        int $noteId,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete_note' . $noteId, $request->request->get('_token'))) {
            $note = $em->getRepository(ClientNote::class)->find($noteId);

            if ($note && $note->getClient() === $client) {
                $em->remove($note);
                $em->flush();

                $this->addFlash('success', 'La note a ete supprimee.');
            }
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/liens/nouveau', name: 'app_admin_clients_lien_new', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addLien(
        Request $request,
        Client $client,
        ClientRepository $clientRepository,
        EntityManagerInterface $em
    ): Response {
        $lien = new ClientLien();
        $lien->setClientSource($client);

        $form = $this->createForm(ClientLienType::class, $lien, [
            'current_client' => $client,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Verifier que le lien n'existe pas deja
            $existing = $em->getRepository(ClientLien::class)->findOneBy([
                'clientSource' => $client,
                'clientCible' => $lien->getClientCible(),
            ]);

            if ($existing) {
                $this->addFlash('warning', 'Ce lien existe deja.');
            } else {
                $em->persist($lien);
                $em->flush();

                $this->addFlash('success', 'Le lien a ete ajoute.');
            }
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout du lien.');
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/liens/{lienId}/supprimer', name: 'app_admin_clients_lien_delete', methods: ['POST'], requirements: ['id' => '\d+', 'lienId' => '\d+'])]
    public function deleteLien(
        Request $request,
        Client $client,
        int $lienId,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete_lien' . $lienId, $request->request->get('_token'))) {
            $lien = $em->getRepository(ClientLien::class)->find($lienId);

            if ($lien && $lien->getClientSource() === $client) {
                $em->remove($lien);
                $em->flush();

                $this->addFlash('success', 'Le lien a ete supprime.');
            }
        }

        return $this->redirectToRoute('app_admin_clients_show', ['id' => $client->getId()]);
    }
}
