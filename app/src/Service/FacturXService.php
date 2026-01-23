<?php

namespace App\Service;

use App\Entity\Facture;
use Atgp\FacturX\Writer;
use Atgp\FacturX\Utils\ProfileHandler;

class FacturXService
{
    public function __construct(
        private PdfFactureService $pdfService
    ) {
    }

    /**
     * Genere un PDF Factur-X (PDF/A-3 avec XML embarque)
     */
    public function generateFacturX(Facture $facture): string
    {
        // 1. Generer le PDF classique via DOMPDF
        $pdfContent = $this->pdfService->generatePdf($facture);

        // 2. Generer le XML Factur-X
        $xml = $this->generateXml($facture);

        // 3. Embarquer le XML dans le PDF
        $writer = new Writer();

        return $writer->generate(
            $pdfContent,
            $xml,
            ProfileHandler::PROFILE_FACTURX_BASIC,
            false // disable XSD validation for now
        );
    }

    /**
     * Retourne le nom de fichier pour le PDF Factur-X
     */
    public function getFilename(Facture $facture): string
    {
        $numero = $facture->getNumero() ?? 'brouillon-' . $facture->getId();
        $prefix = $facture->isAvoir() ? 'avoir' : 'facture';
        return $prefix . '-facturx-' . $numero . '.pdf';
    }

    /**
     * Genere le XML conforme Factur-X BASIC
     */
    private function generateXml(Facture $facture): string
    {
        $typeCode = $facture->isAvoir() ? '381' : '380'; // 380 = Facture, 381 = Avoir
        $dateFacture = $facture->getDateFacture()->format('Ymd');
        $dateEcheance = $facture->getDateEcheance()->format('Ymd');

        // Collecte des lignes et TVA
        $lignesXml = '';
        $totalHt = '0.00';
        $totalTva = '0.00';
        $tvaParTaux = [];

        $lineNumber = 1;
        foreach ($facture->getLignes() as $ligne) {
            $lineHt = $ligne->getTotalHt();
            $lineTva = $ligne->getMontantTva();
            $tauxTva = $ligne->getTauxTva();

            // Accumuler TVA par taux
            if (!isset($tvaParTaux[$tauxTva])) {
                $tvaParTaux[$tauxTva] = ['base' => '0.00', 'montant' => '0.00'];
            }
            $tvaParTaux[$tauxTva]['base'] = bcadd($tvaParTaux[$tauxTva]['base'], $lineHt, 2);
            $tvaParTaux[$tauxTva]['montant'] = bcadd($tvaParTaux[$tauxTva]['montant'], $lineTva, 2);

            $lignesXml .= $this->generateLineItemXml(
                $lineNumber,
                $ligne->getDesignation(),
                $ligne->getQuantite(),
                $ligne->getPrixUnitaire(),
                $lineHt,
                $tauxTva
            );
            $lineNumber++;
        }

        // Generer le XML de TVA par taux
        $tvaXml = '';
        foreach ($tvaParTaux as $taux => $data) {
            $tvaXml .= $this->generateTaxSubtotalXml($data['base'], $data['montant'], $taux);
        }

        $totalHt = $facture->getTotalHt();
        $totalTva = $facture->getTotalTva();
        $totalTtc = $facture->getTotalTtc();

        // Construction du XML complet
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" ';
        $xml .= 'xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100" ';
        $xml .= 'xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:100" ';
        $xml .= 'xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">' . "\n";

        // ExchangedDocumentContext
        $xml .= '<rsm:ExchangedDocumentContext>' . "\n";
        $xml .= '  <ram:GuidelineSpecifiedDocumentContextParameter>' . "\n";
        $xml .= '    <ram:ID>urn:factur-x.eu:1p0:basic</ram:ID>' . "\n";
        $xml .= '  </ram:GuidelineSpecifiedDocumentContextParameter>' . "\n";
        $xml .= '</rsm:ExchangedDocumentContext>' . "\n";

        // ExchangedDocument
        $xml .= '<rsm:ExchangedDocument>' . "\n";
        $xml .= '  <ram:ID>' . $this->escape($facture->getNumero() ?? 'DRAFT-' . $facture->getId()) . '</ram:ID>' . "\n";
        $xml .= '  <ram:TypeCode>' . $typeCode . '</ram:TypeCode>' . "\n";
        $xml .= '  <ram:IssueDateTime>' . "\n";
        $xml .= '    <udt:DateTimeString format="102">' . $dateFacture . '</udt:DateTimeString>' . "\n";
        $xml .= '  </ram:IssueDateTime>' . "\n";
        $xml .= '</rsm:ExchangedDocument>' . "\n";

        // SupplyChainTradeTransaction
        $xml .= '<rsm:SupplyChainTradeTransaction>' . "\n";

        // Lignes de facture
        $xml .= $lignesXml;

        // ApplicableHeaderTradeAgreement
        $xml .= '  <ram:ApplicableHeaderTradeAgreement>' . "\n";

        // Reference commande si presente
        if ($facture->getReferenceCommande()) {
            $xml .= '    <ram:BuyerOrderReferencedDocument>' . "\n";
            $xml .= '      <ram:IssuerAssignedID>' . $this->escape($facture->getReferenceCommande()) . '</ram:IssuerAssignedID>' . "\n";
            $xml .= '    </ram:BuyerOrderReferencedDocument>' . "\n";
        }

        // Vendeur (emetteur)
        $xml .= '    <ram:SellerTradeParty>' . "\n";
        $xml .= '      <ram:Name>' . $this->escape($facture->getEmetteurRaisonSociale()) . '</ram:Name>' . "\n";
        if ($facture->getEmetteurSiren()) {
            $xml .= '      <ram:SpecifiedLegalOrganization>' . "\n";
            $xml .= '        <ram:ID schemeID="0002">' . $this->escape($facture->getEmetteurSiren()) . '</ram:ID>' . "\n";
            $xml .= '      </ram:SpecifiedLegalOrganization>' . "\n";
        }
        $xml .= '      <ram:PostalTradeAddress>' . "\n";
        $xml .= '        <ram:CountryID>FR</ram:CountryID>' . "\n";
        if ($facture->getEmetteurAdresse()) {
            $xml .= '        <ram:LineOne>' . $this->escape($this->getFirstLine($facture->getEmetteurAdresse())) . '</ram:LineOne>' . "\n";
        }
        $xml .= '      </ram:PostalTradeAddress>' . "\n";
        if ($facture->getEmetteurTva()) {
            $xml .= '      <ram:SpecifiedTaxRegistration>' . "\n";
            $xml .= '        <ram:ID schemeID="VA">' . $this->escape($facture->getEmetteurTva()) . '</ram:ID>' . "\n";
            $xml .= '      </ram:SpecifiedTaxRegistration>' . "\n";
        }
        $xml .= '    </ram:SellerTradeParty>' . "\n";

        // Acheteur (client)
        $xml .= '    <ram:BuyerTradeParty>' . "\n";
        $xml .= '      <ram:Name>' . $this->escape($facture->getClientRaisonSociale()) . '</ram:Name>' . "\n";
        if ($facture->getClientSiret()) {
            $xml .= '      <ram:SpecifiedLegalOrganization>' . "\n";
            $xml .= '        <ram:ID schemeID="0002">' . $this->escape($facture->getClientSiret()) . '</ram:ID>' . "\n";
            $xml .= '      </ram:SpecifiedLegalOrganization>' . "\n";
        }
        $xml .= '      <ram:PostalTradeAddress>' . "\n";
        $xml .= '        <ram:CountryID>' . $this->escape($facture->getClientCodePays()) . '</ram:CountryID>' . "\n";
        if ($facture->getClientAdresse()) {
            $xml .= '        <ram:LineOne>' . $this->escape($this->getFirstLine($facture->getClientAdresse())) . '</ram:LineOne>' . "\n";
        }
        $xml .= '      </ram:PostalTradeAddress>' . "\n";
        if ($facture->getClientTva()) {
            $xml .= '      <ram:SpecifiedTaxRegistration>' . "\n";
            $xml .= '        <ram:ID schemeID="VA">' . $this->escape($facture->getClientTva()) . '</ram:ID>' . "\n";
            $xml .= '      </ram:SpecifiedTaxRegistration>' . "\n";
        }
        $xml .= '    </ram:BuyerTradeParty>' . "\n";
        $xml .= '  </ram:ApplicableHeaderTradeAgreement>' . "\n";

        // ApplicableHeaderTradeDelivery
        $xml .= '  <ram:ApplicableHeaderTradeDelivery/>' . "\n";

        // ApplicableHeaderTradeSettlement
        $xml .= '  <ram:ApplicableHeaderTradeSettlement>' . "\n";
        $xml .= '    <ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>' . "\n";

        // Conditions de paiement
        $xml .= '    <ram:SpecifiedTradePaymentTerms>' . "\n";
        $xml .= '      <ram:DueDateDateTime>' . "\n";
        $xml .= '        <udt:DateTimeString format="102">' . $dateEcheance . '</udt:DateTimeString>' . "\n";
        $xml .= '      </ram:DueDateDateTime>' . "\n";
        $xml .= '    </ram:SpecifiedTradePaymentTerms>' . "\n";

        // TVA
        $xml .= $tvaXml;

        // Totaux
        $xml .= '    <ram:SpecifiedTradeSettlementHeaderMonetarySummation>' . "\n";
        $xml .= '      <ram:LineTotalAmount>' . $totalHt . '</ram:LineTotalAmount>' . "\n";
        $xml .= '      <ram:TaxBasisTotalAmount>' . $totalHt . '</ram:TaxBasisTotalAmount>' . "\n";
        $xml .= '      <ram:TaxTotalAmount currencyID="EUR">' . $totalTva . '</ram:TaxTotalAmount>' . "\n";
        $xml .= '      <ram:GrandTotalAmount>' . $totalTtc . '</ram:GrandTotalAmount>' . "\n";
        $xml .= '      <ram:DuePayableAmount>' . $totalTtc . '</ram:DuePayableAmount>' . "\n";
        $xml .= '    </ram:SpecifiedTradeSettlementHeaderMonetarySummation>' . "\n";

        $xml .= '  </ram:ApplicableHeaderTradeSettlement>' . "\n";
        $xml .= '</rsm:SupplyChainTradeTransaction>' . "\n";
        $xml .= '</rsm:CrossIndustryInvoice>';

        return $xml;
    }

    /**
     * Genere le XML pour une ligne de facture
     */
    private function generateLineItemXml(
        int $lineNumber,
        string $designation,
        string $quantite,
        string $prixUnitaire,
        string $totalHt,
        string $tauxTva
    ): string {
        $xml = '  <ram:IncludedSupplyChainTradeLineItem>' . "\n";
        $xml .= '    <ram:AssociatedDocumentLineDocument>' . "\n";
        $xml .= '      <ram:LineID>' . $lineNumber . '</ram:LineID>' . "\n";
        $xml .= '    </ram:AssociatedDocumentLineDocument>' . "\n";
        $xml .= '    <ram:SpecifiedTradeProduct>' . "\n";
        $xml .= '      <ram:Name>' . $this->escape($designation) . '</ram:Name>' . "\n";
        $xml .= '    </ram:SpecifiedTradeProduct>' . "\n";
        $xml .= '    <ram:SpecifiedLineTradeAgreement>' . "\n";
        $xml .= '      <ram:NetPriceProductTradePrice>' . "\n";
        $xml .= '        <ram:ChargeAmount>' . $prixUnitaire . '</ram:ChargeAmount>' . "\n";
        $xml .= '      </ram:NetPriceProductTradePrice>' . "\n";
        $xml .= '    </ram:SpecifiedLineTradeAgreement>' . "\n";
        $xml .= '    <ram:SpecifiedLineTradeDelivery>' . "\n";
        $xml .= '      <ram:BilledQuantity unitCode="C62">' . $quantite . '</ram:BilledQuantity>' . "\n";
        $xml .= '    </ram:SpecifiedLineTradeDelivery>' . "\n";
        $xml .= '    <ram:SpecifiedLineTradeSettlement>' . "\n";
        $xml .= '      <ram:ApplicableTradeTax>' . "\n";
        $xml .= '        <ram:TypeCode>VAT</ram:TypeCode>' . "\n";
        $xml .= '        <ram:CategoryCode>S</ram:CategoryCode>' . "\n";
        $xml .= '        <ram:RateApplicablePercent>' . $tauxTva . '</ram:RateApplicablePercent>' . "\n";
        $xml .= '      </ram:ApplicableTradeTax>' . "\n";
        $xml .= '      <ram:SpecifiedTradeSettlementLineMonetarySummation>' . "\n";
        $xml .= '        <ram:LineTotalAmount>' . $totalHt . '</ram:LineTotalAmount>' . "\n";
        $xml .= '      </ram:SpecifiedTradeSettlementLineMonetarySummation>' . "\n";
        $xml .= '    </ram:SpecifiedLineTradeSettlement>' . "\n";
        $xml .= '  </ram:IncludedSupplyChainTradeLineItem>' . "\n";

        return $xml;
    }

    /**
     * Genere le XML pour un sous-total TVA
     */
    private function generateTaxSubtotalXml(string $baseHt, string $montantTva, string $taux): string
    {
        $xml = '    <ram:ApplicableTradeTax>' . "\n";
        $xml .= '      <ram:CalculatedAmount>' . $montantTva . '</ram:CalculatedAmount>' . "\n";
        $xml .= '      <ram:TypeCode>VAT</ram:TypeCode>' . "\n";
        $xml .= '      <ram:BasisAmount>' . $baseHt . '</ram:BasisAmount>' . "\n";
        $xml .= '      <ram:CategoryCode>S</ram:CategoryCode>' . "\n";
        $xml .= '      <ram:RateApplicablePercent>' . $taux . '</ram:RateApplicablePercent>' . "\n";
        $xml .= '    </ram:ApplicableTradeTax>' . "\n";

        return $xml;
    }

    /**
     * Echappe les caracteres speciaux XML
     */
    private function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Retourne la premiere ligne d'une adresse multilignes
     */
    private function getFirstLine(string $address): string
    {
        $lines = explode("\n", $address);
        return trim($lines[0] ?? '');
    }
}
