<?php
/**
 * Import.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;


class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAndParse()
    {
        $config = $this->getMock(\FMUP\Import\Config::class);
        $import = $this->getMock(\FMUP\Import::class, array('parse'), array(__FILE__, $config));
        /**
         * @var $config \FMUP\Import\Config
         * @var $import \FMUP\Import
         */
        $import->parse();
    }
}
