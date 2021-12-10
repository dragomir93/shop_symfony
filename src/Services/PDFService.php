<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFService {

    public function getPDF($html) 
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVu Sans');
        $pdfOptions->set('isRemoteEnabled',true);

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
       
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        ob_get_clean();

        $date = new \DateTime("now");
        $string = $date->format('YmdHis');

        // Output the generated PDF to Browser (force download)
        $dompdf->stream($string, [
            "Attachment" => false
        ]); 
    }
}