<?php
/**
 * Import.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;


class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAndParse()
    {
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)->getMock();
        $import = $this->getMockBuilder(\FMUP\Import::class)
            ->setMethods(array('parse'))
            ->setConstructorArgs(array(__FILE__, $config))
            ->getMock();
        /**
         * @var $config \FMUP\Import\Config
         * @var $import \FMUP\Import
         */
        $import->parse();
    }
}
