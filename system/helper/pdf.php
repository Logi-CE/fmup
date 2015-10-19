<?php
new Component('tcpdf/config/lang/fra');
new Component('tcpdf/tcpdf');
/**
 * Classe générant un PDF grâce à la bibliothèque TCPDF
 * @author afalaise
 * @version 4.0
 */
class Pdf extends TCPDF
{
    protected $titre;
    
    public function __construct ($titre, $entetes = true, $sous_titre = false)
    {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        if (!$sous_titre) {
            $sous_titre = $titre;
        }
        $this->titre = $sous_titre;
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor(Config::paramsVariables('title_back'));
        $this->SetTitle($titre);
        $this->SetSubject($titre);
        $this->SetKeywords('TLN, PDF, '.$titre);
        $this->setPrintHeader($entetes);
        $this->setPrintFooter($entetes);
        $this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $titre, '');
        $this->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_HEADER, PDF_MARGIN_RIGHT);
        $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        if (isset($l) && $l) {
            $this->setLanguagearray($l);
        }
        $this->setFontSubsetting(true);
        $this->SetFont('times', '', 11);
        
        $this->AddPage();
    }
    
	/**
     * Entete commune du PDF
     */
    public function Header()
    {
        // Logo
        $image_file = Config::paramsVariables('public_path').Constantes::getSrcLogo();
        $this->Image($image_file, 15, 10, 50, '', 'GIF', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->MultiCell(0, 30, '<h2 style="border:1px solid grey; font-size:45px; color:#000;">'.$this->titre.'</h2>', 0, 'C', false, 1, '', '30px', true, 0, true, true, 0, 'T', false);
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_HEADER + 30, PDF_MARGIN_RIGHT);
    }

    /**
     * Pied de page cummun du PDF
     */
    public function Footer()
    {
        // Position à 25 mm de la fin de page
        $this->SetY(-25);
    }
    
    /**
     * Page faisant partie du corps du PDF
     */
    public function chargerTemplate ($vue, $params = array(), $configs = array())
    {
        ob_start();
        new View($vue, $params, $configs);
        $contenu = ob_get_clean();
        $this->WriteHTML($contenu, true, false, false, true, '');
        $this->lastPage();
    }
}
