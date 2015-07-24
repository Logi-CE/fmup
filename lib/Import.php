<?php
namespace FMUP;

use FMUP\Import\Iterator\FieldValideIterator;
use FMUP\Import\Iterator\LineFilterIterator;
use FMUP\Import\Iterator\LineToConfigIterator;
use FMUP\Import\Iterator\FileIterator;
use FMUP\Import\Iterator\DoublonIterator;

abstract class Import
{

    protected $fileIterator;

    protected $config;

    public function __construct($file_name, \FMUP\Import\Config $config)
    {
        $this->fileIterator = new FileIterator($file_name);
        $this->config = $config;
    }

    public abstract function parse ();
}
?>