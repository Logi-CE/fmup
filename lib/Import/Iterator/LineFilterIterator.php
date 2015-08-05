<?php
namespace FMUP\Import\Iterator;

use FMUP\Import\Config;

/**
 * Ne retourne que les lignes validées
 *
 * @author csanz
 *
 */
class LineFilterIterator extends \FilterIterator
{

    public function __construct(\Iterator $iterator)
    {
        parent::__construct($iterator);
    }

    private $config;

    public function current()
    {
        return $this->getInnerIterator()->current();
    }

    public function accept()
    {
        $config = $this->getInnerIterator()->current();
        if (!$config) {
            return false;
        }
        return $config->validateLine();
    }
}
