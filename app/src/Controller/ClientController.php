<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\ClientLien;
use App\Entity\ClientNote;
use App\Entity\Contact;
use App\Form\ClientLienType;
use App\Form\ClientNoteType;
use App\Form\ClientType;
use App\Form\ContactType;
use App\Repository\ClientLienRepository;
use App\Repository\ClientNoteRepository;
use App\Repository\ClientRepository;
use App\Repository\ContactRepository;
use App\Service\CsvExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function index(Request $request, ClientRepository $clientRepository): Response
    {
        $search = $request->query->get('search');
        $actifFilter = $request->query->get('actif');
        $sort = $request->query->get('sort', 'raisonSociale');
        $direction = $request->query->get('direction', 'ASC');
        $page = max(1, $request->query->getInt('page', 1));

        $actif = null;
        if ($actifFilter === '1') {
            $actif = true;
        } elseif ($actifFilter === '0') {
            $actif = false;
        }

        $paginator = $clientRepository->searchWithFilters($search, $actif, $sort, $direction, $page);
        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / 20);

        return $this->render('client/index.html.twig', [
            'clients' => $paginator,
            'search' => $search,
            'actifFilter' => $actifFilter,
            'sort' => $sort,
            'direction' => $direction,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

    #[Route('/export', name: 'app_client_export', methods: ['GET'])]
    public function export(Request $request, ClientRepository $clientRepository, CsvExportService $csvExportService): Response
    {
        $search = $request->query->get('search');
        $actifFilter = $request->query->get('actif');

        $actif = null;
        if ($actifFilter === '1') {
            $actif = true;
        } elseif ($actifFilter === '0') {
            $actif = false;
        }

        $clients = $clientRepository->findAllForExport($search, $actif);

        return $csvExportService->exportClients($clients);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();

            $this->addFlash('success', 'Client cree avec succes.');

            return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Client $client): Response
    {
        $contactForm = $this->createForm(ContactType::class, new Contact());
        $noteForm = $this->createForm(ClientNoteType::class, new ClientNote());
        $lienForm = $this->createForm(ClientLienType::class, new ClientLien(), [
            'current_client' => $client,
        ]);

        return $this->render('client/show.html.twig', [
            'client' => $client,
            'contactForm' => $contactForm,
            'noteForm' => $noteForm,
            'lienForm' => $lienForm,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Client modifie avec succes.');

            return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_client_toggle', methods: ['POST'])]
    public function toggleActif(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle' . $client->getId(), $request->getPayload()->getString('_token'))) {
            $client->setActif(!$client->isActif());
            $entityManager->flush();

            $status = $client->isActif() ? 'active' : 'desactive';
            $this->addFlash('success', 'Client ' . $status . ' avec succes.');
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/contacts/new', name: 'app_client_contact_new', methods: ['POST'])]
    public function addContact(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setClient($client);

            if ($contact->isPrincipal()) {
                foreach ($client->getContacts() as $existingContact) {
                    $existingContact->setPrincipal(false);
                }
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Contact ajoute avec succes.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout du contact.');
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/contacts/{contactId}/delete', name: 'app_client_contact_delete', methods: ['POST'])]
    public function deleteContact(
        Request $request,
        Client $client,
        int $contactId,
        ContactRepository $contactRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $contact = $contactRepository->find($contactId);

        if ($contact && $contact->getClient() === $client) {
            if ($this->isCsrfTokenValid('delete_contact' . $contactId, $request->getPayload()->getString('_token'))) {
                $entityManager->remove($contact);
                $entityManager->flush();

                $this->addFlash('success', 'Contact supprime avec succes.');
            }
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/contacts/{contactId}/principal', name: 'app_client_contact_principal', methods: ['POST'])]
    public function setPrincipal(
        Request $request,
        Client $client,
        int $contactId,
        ContactRepository $contactRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $contact = $contactRepository->find($contactId);

        if ($contact && $contact->getClient() === $client) {
            if ($this->isCsrfTokenValid('principal_contact' . $contactId, $request->getPayload()->getString('_token'))) {
                foreach ($client->getContacts() as $existingContact) {
                    $existingContact->setPrincipal(false);
                }

                $contact->setPrincipal(true);
                $entityManager->flush();

                $this->addFlash('success', 'Contact principal defini avec succes.');
            }
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/notes/new', name: 'app_client_note_new', methods: ['POST'])]
    public function addNote(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $note = new ClientNote();
        $form = $this->createForm(ClientNoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setClient($client);
            $entityManager->persist($note);
            $entityManager->flush();

            $this->addFlash('success', 'Note ajoutee avec succes.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout de la note.');
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/notes/{noteId}/delete', name: 'app_client_note_delete', methods: ['POST'])]
    public function deleteNote(
        Request $request,
        Client $client,
        int $noteId,
        ClientNoteRepository $noteRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $note = $noteRepository->find($noteId);

        if ($note && $note->getClient() === $client) {
            if ($this->isCsrfTokenValid('delete_note' . $noteId, $request->getPayload()->getString('_token'))) {
                $entityManager->remove($note);
                $entityManager->flush();

                $this->addFlash('success', 'Note supprimee avec succes.');
            }
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/liens/new', name: 'app_client_lien_new', methods: ['POST'])]
    public function addLien(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        $lien = new ClientLien();
        $form = $this->createForm(ClientLienType::class, $lien, [
            'current_client' => $client,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lien->setClientSource($client);
            $entityManager->persist($lien);
            $entityManager->flush();

            $this->addFlash('success', 'Lien ajoute avec succes.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout du lien.');
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }

    #[Route('/{id}/liens/{lienId}/delete', name: 'app_client_lien_delete', methods: ['POST'])]
    public function deleteLien(
        Request $request,
        Client $client,
        int $lienId,
        ClientLienRepository $lienRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $lien = $lienRepository->find($lienId);

        if ($lien && $lien->getClientSource() === $client) {
            if ($this->isCsrfTokenValid('delete_lien' . $lienId, $request->getPayload()->getString('_token'))) {
                $entityManager->remove($lien);
                $entityManager->flush();

                $this->addFlash('success', 'Lien supprime avec succes.');
            }
        }

        return $this->redirectToRoute('app_client_show', ['id' => $client->getId()]);
    }
}
