<?php

namespace App\Service;

use App\Entity\Facture;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfFactureService
{
    public function __construct(
        private Environment $twig
    ) {
    }

    public function generatePdf(Facture $facture): string
    {
        // 1. Rendre le template Twig en HTML
        $html = $this->twig->render('facturation/pdf.html.twig', [
            'facture' => $facture,
        ]);

        // 2. Configurer Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        // 3. Generer le PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function getFilename(Facture $facture): string
    {
        $numero = $facture->getNumero() ?? 'brouillon-' . $facture->getId();
        return 'facture-' . $numero . '.pdf';
    }
}
