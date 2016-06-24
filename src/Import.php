<?php
namespace FMUP;

use FMUP\Import as ImportNamespace;

abstract class Import
{
    protected $fileIterator;
    protected $config;

    public function __construct($fileName, ImportNamespace\Config $config)
    {
        $this->fileIterator = new ImportNamespace\Iterator\FileIterator($fileName);
        $this->config = $config;
    }

    abstract public function parse();
}
