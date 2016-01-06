<?php
namespace FMUP\Import\Iterator;

/**
 * Remplie les valeurs d'une Config à partir d'une ligne du fichier
 *
 * @author jyamin
 *
 */
class CsvToConfigIterator extends \IteratorIterator
{

    private $config;

    public function __construct(\Iterator $fIterator, \FMUP\Import\Config $config)
    {
        parent::__construct($fIterator);
        $this->config = $config;
    }

    public function current()
    {
        $liste_champ = $this->getInnerIterator()->current();
        if (count($liste_champ) > 1) {   
            foreach ($liste_champ as $key => $champ) {
                $field = $this->config->getField($key);
                $field->setValue($champ);
            }
            return $this->config;
        } else {
            return null;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }
}
