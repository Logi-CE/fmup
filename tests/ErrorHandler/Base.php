<?php
/**
 * Base.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\ErrorHandler;


class BaseTest extends \PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $base = new \FMUP\ErrorHandler\Base();
        $this->assertInstanceOf(\FMUP\ErrorHandler::class, $base);
        $this->assertSame($base, $base->init());
    }
}
