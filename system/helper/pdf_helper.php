<?php
class PdfHelper
{
    public static function InitializePdf($template = '')
    {
        $pdf = new PDF('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        if ($template != '') {
            $pdf->setSourceFile('./../../data/templates/'.$template.'.pdf');
            $tplidx = $pdf->importPage(1);
            $pdf->useTemplate($tplidx/*, 10, 0, 200*/);
        }
        return $pdf;
    }
}

class TCPdfHelper
{
    public static function InitializePdf($title = '', $subject = '', $keywords = '', $author = 'ODAC3E')
    {
        if (!class_exists('TCPDF')) {
            throw new \LogicException('TCPDF must be installed');
        }
        $pdf = new TCPDF("P", "mm", "A4", true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($author);
        if (!empty($title))		$pdf->SetTitle($title);
        if (!empty($subject))	$pdf->SetSubject($subject);
        if (!empty($keywords))	$pdf->SetKeywords($keywords);

        // set default header data
        //$pdf->SetHeaderData('logo.png', $width, 'OCAD3E', '');

        // set header and footer fonts
        $pdf->setHeaderFont(array('helvetica', 'B', 'helvetica'));
        $pdf->setFooterFont(array('helvetica', 'B', 'helvetica'));

        // set default monospaced font
        //$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //set some language-dependent strings, $l is configure in ../config/lang/fra.php
        $pdf->setLanguageArray($l);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        $pdf->SetFont('helvetica', '', 14, '', true);
        return $pdf;
    }

    public static function generatePdf($template, array $vars, $file)
    {
        // Initialise le PDF
        $pdf = TCPdfHelper::InitializePdf();

        // Recupèration de la template
        $html = TemplateHelper::getTemplatePdf($template);

        // remplacement des clefs par leurs valeurs correspondante

        if (!empty($vars)) {
            foreach ($vars as $search => $replace) {
                $html = str_replace($search, $replace, $html);
            }
        }
        // Écrit le template HTML dans le PDF

        $pdf->writeHTML($html);

        // Créer le fichier pdf
        $pdf->Output("/data/documents/".$file, 'I');
    }
}

class PDF extends FPDI
{
    public $adresse_OCAD3E_1 = '95 rue de la Boétie';
    public $adresse_OCAD3E_2 = '75008 Paris';
    public $telephone = '0811 007 260';
    public $email = 'secretariat@ocad3e.com';

    public function Header()
    {
        $this->SetLeftMargin(28);

        //OCAD3E
        $this->SetFont('helvetica', 'b', 12);
        $this->setY(14);
        $this->Cell(39, 3, utf8_decode('OCAD3E'), 0, 1, 'C');

        //Séparateur
        $this->SetLineWidth(0.5);
        $this->Line(28, 18, 67, 18);

        //titre et adresse
        $text = 'Organisme Coordonnateur Agréé'."\n";
        $text .= 'Par Arrêté du 23 décembre 2009'."\n";
        $text .= $this->adresse_OCAD3E_1."\n".$this->adresse_OCAD3E_2;
        $this->SetFont('helvetica', '', 7);
        $this->Ln(2);
        $this->MultiCell(39, 3, utf8_decode($text), 0, 'C');
    }

    public function Footer()
    {
        //Séparateur
        $this->SetLineWidth(0.4);
        $this->Line(25, 268, 186, 268);

        //titre
        $text = 'Etat trimestriel des versements';
        $this->SetFont('helvetica', '', 11);
        $this->SetY(-28);
        $this->Cell(0, 4, utf8_decode($text), 0, 1, 'C');
        $this->SetXY(-24, -28);
        $this->Cell(5, 5, $this->PageNo().' / {nb}', 0, 0, 'R');

        //Adresse
        $this->SetY(-19);
        $this->SetFont('helvetica', '', 10);
        $text = 'OCAD3E - '.$this->adresse_OCAD3E_1.' - '.$this->adresse_OCAD3E_2."\n";
        $text .= 'Tél : '.$this->telephone.'. Email : '.$this->email;
        $this->MultiCell(0, 5, utf8_decode($text), 0, 'C');
    }
}
