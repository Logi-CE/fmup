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

    /**
     * @return \FMUP\Import\Config
     */
    public function current()
    {
        $listFields = $this->getInnerIterator()->current();
        $countConfigListField = count($this->config->getListeField());
        $countOriginalListFields = count($listFields);
        if ($countOriginalListFields > 1) {
            $i = 0;
            foreach ($listFields as $fieldValue) {
                if ($i >= $countConfigListField) {
                    break;
                }
                $this->config->getField($i++)->setValue($fieldValue);
            }
            for (; $i < $countConfigListField; $i++) {
                $this->config->getField($i)->setValue(null);
            }
            return $this->config;
        } else {
            for ($i = 0; $i < $countConfigListField; $i++) {
                $this->config->getField($i)->setValue(null);
            }
            return $this->config;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }
}
