<?php
namespace FMUP;

use FMUP\Import\Iterator\FileIterator;

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