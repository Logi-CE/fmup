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
        $listFields = $this->getInnerIterator()->current();
        if (count($listFields) > 1) {
            foreach ($listFields as $key => $champ) {
                if ($key >= count($this->config->getListeField())) {
                    break;
                }
                $field = $this->config->getField($key);
                $field->setValue($champ);
            }
            for ($i = count($listFields) ; $i <  count($this->config->getListeField()) ; $i++) {
                $field = $this->config->getField($i);
                $field->setValue(null);
            }
            return $this->config;
        } else {
            for ($i = 0 ; $i <  count($this->config->getListeField()) ; $i++) {
                $field = $this->config->getField($i);
                $field->setValue(null);
            }
            return $this->config;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }
}
