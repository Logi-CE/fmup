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
        $config = $this->getMockBuilder('\FMUP\Import\Config')->getMock();
        $import = $this->getMockBuilder('\FMUP\Import')
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
