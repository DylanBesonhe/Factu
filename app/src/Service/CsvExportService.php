<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Contrat;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * @param iterable<Client> $clients
     */
    public function exportClients(iterable $clients): StreamedResponse
    {
        $response = new StreamedResponse(function() use ($clients) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Headers
            fputcsv($handle, [
                'Code',
                'Raison sociale',
                'SIREN',
                'Adresse',
                'Email',
                'Telephone',
                'IBAN',
                'BIC',
                'Actif',
                'Date creation'
            ], ';');

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->getCode(),
                    $client->getRaisonSociale(),
                    $client->getSiren(),
                    $this->cleanAddress($client->getAdresse()),
                    $client->getEmail(),
                    $client->getTelephone(),
                    $client->getIban(),
                    $client->getBic(),
                    $client->isActif() ? 'Oui' : 'Non',
                    $client->getCreatedAt()?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="clients_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    /**
     * @param iterable<Contrat> $contrats
     */
    public function exportContrats(iterable $contrats): StreamedResponse
    {
        $response = new StreamedResponse(function() use ($contrats) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Headers
            fputcsv($handle, [
                'Numero',
                'Client',
                'Instance',
                'Emetteur',
                'Date signature',
                'Date anniversaire',
                'Periodicite',
                'Statut',
                'Nb licences',
                'Total HT',
                'Facture particuliere',
                'Date creation'
            ], ';');

            foreach ($contrats as $contrat) {
                fputcsv($handle, [
                    $contrat->getNumero(),
                    $contrat->getClient()?->getRaisonSociale() ?? '',
                    $contrat->getInstance()?->getNomActuel() ?? '',
                    $contrat->getEmetteur()?->getNom() ?? '',
                    $contrat->getDateSignature()?->format('d/m/Y') ?? '',
                    $contrat->getDateAnniversaire()?->format('d/m/Y') ?? '',
                    $contrat->getPeriodiciteLabel(),
                    $contrat->getStatutLabel(),
                    $contrat->getNbLicences(),
                    number_format((float) $contrat->getTotalHt(), 2, ',', ' ') . ' €',
                    $contrat->isFactureParticuliere() ? 'Oui' : 'Non',
                    $contrat->getCreatedAt()?->format('d/m/Y') ?? '',
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="contrats_' . date('Y-m-d') . '.csv"');

        return $response;
    }

    private function cleanAddress(?string $address): string
    {
        if ($address === null) {
            return '';
        }
        return str_replace(["\r\n", "\r", "\n"], ' ', $address);
    }
}
