<?php
namespace FMUP\Import\Iterator;

/**
 * Remplie les valeurs d'une Config à partir d'une ligne du fichier
 *
 * @author csanz
 *
 */
class LineToConfigIterator extends \IteratorIterator
{
    private $config;

    public function __construct(\Iterator $fIterator, \FMUP\Import\Config $config)
    {
        parent::__construct($fIterator);
        $this->config = $config;
    }

    public function current()
    {
        $filedList = explode(";", $this->getInnerIterator()->current());
        if (count($filedList) > 1) {
            foreach ($filedList as $key => $champ) {
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
