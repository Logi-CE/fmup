<?php
/**
 * Pragma.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class PragmaTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $pragma = new \FMUP\Response\Header\Pragma(\FMUP\Response\Header\Pragma::MODE_CACHE);
        $this->assertInstanceOf(\FMUP\Response\Header::class, $pragma);
        $this->assertSame(\FMUP\Response\Header\Pragma::MODE_CACHE, $pragma->getMode());

        $pragma = new \FMUP\Response\Header\Pragma(\FMUP\Response\Header\Pragma::MODE_NOCACHE);
        $this->assertInstanceOf(\FMUP\Response\Header::class, $pragma);
        $this->assertSame(\FMUP\Response\Header\Pragma::MODE_NOCACHE, $pragma->getMode());
        $this->assertSame(\FMUP\Response\Header\Pragma::MODE_NOCACHE, $pragma->getValue());
    }

    public function testSetMode()
    {
        $pragma = new \FMUP\Response\Header\Pragma(\FMUP\Response\Header\Pragma::MODE_CACHE);
        $this->assertSame($pragma, $pragma->setMode('unitTest'));
        $this->assertSame('unitTest', $pragma->getMode());
        $this->assertSame('unitTest', $pragma->getValue());
    }

    public function testGetType()
    {
        $pragma = new \FMUP\Response\Header\Pragma(\FMUP\Response\Header\Pragma::MODE_CACHE);
        $this->assertSame(\FMUP\Response\Header\Pragma::TYPE, $pragma->getType());
    }
}
