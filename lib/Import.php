<?php
namespace FMU;

use FMUP\Import\Iterator\FieldValideIterator;
use FMUP\Import\Iterator\LineFilterIterator;
use FMUP\Import\Iterator\LineToConfigIterator;
use FMUP\Import\Iterator\FileIterator;
use FMUP\Import\Iterator\DoublonIterator;

class Import
{

    private $fileIterator;

    private $config;

    public function __construct($file_name, \FMUP\Import\Config $config)
    {
        $this->fileIterator = new FileIterator($file_name);
        $this->config = $config;
    }

    public function parse ()
    {
        $lci = new LineToConfigIterator($this->fileIterator, $this->config);
        $di = new DoublonIterator($lci);
        $lvi = new LineFilterIterator($lci);
        
        foreach ($di as $key => $value) {
            echo "key : ".$key."\n";
            echo "config : ".$value."\n";
            if ($value) {
                echo $value->validateLine();
                echo "\n doublon ligne : ".$value->getDoublonLigne() . "\n";
            }
        }
    }
}
?>