<?php

use \setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfReader\PdfReaderException;

/**
 * @param $dateInvoice
 * @return string
 */
function prepareInvoiceByDate($dateInvoice): string
{
    $invoices = getUserInvoices();
    $invoice = $invoices[$dateInvoice];
    header("Content-type: application/pdf");
    $wpUser = wp_get_current_user();
    $methodePayment = getUserMeta('paysubs_card_effectiveBrand')[0];
    $planId = getUserMeta('paysubs_plan')[0];
    $plan = new Payzen_Subscribers_Plan($planId);

    // initiate FPDI
    $pdf = new Fpdi();
    // add a page
    $pdf->AddPage();
    try {
        $pdf->setSourceFile(plugin_dir_path(dirname(__FILE__)) . 'assets/invoice.pdf');
        // import page 1
        $tplId = $pdf->importPage(1);
        // use the imported page and place it at point 10,10 with a width of 100 mm
        $pdf->useTemplate($tplId, ['adjustPageSize' => true]);
        $pdf->SetFont('Helvetica','B',13);
        $pdf->SetY(32);
        $pdf->Cell(106);
        $pdf->Cell(50, 5, "ABO-" . date('d-m-Y', strtotime($dateInvoice)), 0, 1, 'L');
        $pdf->SetY(45.5);
        $pdf->Cell(78);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(50, 5, date('d-m-Y', strtotime($dateInvoice)), 0, 1, 'L');
        $pdf->SetY(45.5);
        $pdf->Cell(110);
        $pdf->Cell(50, 5, iconv('UTF-8', 'windows-1252',mb_strtoupper($wpUser->get('display_name'))), 0, 1, 'L');
        if($invoice['mode'] === 'TEST') {
            $pdf->SetY(60);
            $pdf->Cell(110);
            $pdf->Cell(50, 5, 'Mode ' . $invoice['mode'], 0, 1, 'L');
        }
        $pdf->SetY(92);
        $pdf->Cell(78);
        $pdf->Cell(50, 5, iconv('UTF-8', 'windows-1252',mb_strtoupper($wpUser->get('display_name'))), 0, 1, 'L');
        $pdf->SetY(121.2);
        $pdf->Cell(113);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(50, 5, $plan->getFrequencyTranslate(), 0, 1, 'L');
        $pdf->SetY(125.7);
        $pdf->Cell(109);
        $pdf->Cell(50, 5, ($methodePayment === 'SDD' ?
            iconv('UTF-8', 'windows-1252',__('Bank direct debit','payzen-subscribers')) :
            $methodePayment), 0, 1, 'L');
        $pdf->SetY(153);
        $pdf->Cell(61);
        $pdf->Cell(50, 5, date('d/m/Y', strtotime($dateInvoice)), 0, 1, 'L');
        $pdf->SetY(153);
        $pdf->Cell(86);
        $pdf->Cell(50, 5, date('d/m/Y', strtotime(getDelay($plan->getFrequency()),strtotime($dateInvoice))), 0, 1, 'L');
        $pdf->SetY(153);
        $pdf->Cell(135);
        $pdf->Cell(50, 5, number_format($invoice['orderTotalAmount']/100, 2,',', ' ') .
            iconv('UTF-8', 'windows-1252',($invoice['orderCurrency'] === 'EUR' ? ' €' : ' $')), 0, 1, 'L');
        $pdf->SetY(153);
        $pdf->Cell(155);
        $pdf->Cell(50, 5, number_format($invoice['orderTotalAmount']/100, 2,',', ' ') .
            iconv('UTF-8', 'windows-1252',($invoice['orderCurrency'] === 'EUR' ? ' €' : ' $')), 0, 1, 'L');
        $pdf->SetY(158.1);
        $pdf->Cell(56);
        $pdf->SetFont('Helvetica','',11);
        $pdf->Cell(50, 5,strtolower($plan->getFrequencyTranslate()), 0, 1, 'L');
        $pdf->SetY(158.3);
        $pdf->Cell(78);
        $pdf->SetFont('Helvetica','',9);
        $pdf->Cell(50, 5, number_format($invoice['orderTotalAmount']/100, 2,',', ' ') .
            iconv('UTF-8', 'windows-1252',($invoice['orderCurrency'] === 'EUR' ? '€' : '$')), 0, 1, 'L');
        $pdf->SetY(208.7);
        $pdf->Cell(156);
        $pdf->SetFont('Helvetica','B',9);
        $pdf->Cell(50, 5, number_format($invoice['orderTotalAmount']/100, 2,',', ' ') .
            iconv('UTF-8', 'windows-1252',($invoice['orderCurrency'] === 'EUR' ? ' €' : ' $')), 0, 1, 'L');
    } catch (PdfParserException $e) {
    } catch (PdfReaderException $e) {
    }
    return $pdf->Output(__('Invoice','payzen-subscribers') . '-' . date('Y-m-d', strtotime($dateInvoice)) . '.pdf', "D");
}