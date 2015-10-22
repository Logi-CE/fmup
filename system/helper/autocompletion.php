<?php
/**
 * Controleur de base des appels AJAX par autocompetion
 * Il doit être surchargé pour être utilisé
 * @author afalaise
 */
class Autocompletion extends \FMUP\Controller
{
    protected function genererXml ($objets, $identifiant, $texte, $info = false)
    {
        $xml = $this->initXml();

        foreach ($objets as $objet) {
            $xml->startElement('rs');
            $xml->writeAttribute('id', $objet->getAttribute($identifiant));
            if ($info) {
                $xml->writeAttribute('info', $objet->getAttribute($info));
            } else {
                $xml->writeAttribute('info', '');
            }
            $xml->text($objet->getAttribute($texte));
            $xml->endElement();
        }
        $xml->endDocument();

        echo $xml->outputMemory(true);
    }
    
    protected function initXml()
    {
        $xml = new XmlWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('results');
        return $xml;
    }
    
    public function getActionMethod($action)
    {
        return $action;
    }
}
