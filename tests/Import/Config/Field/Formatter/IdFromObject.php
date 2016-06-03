<?php
namespace Tests\Import\Config\Field\Formatter;

class IdFromObject extends \PHPUnit_Framework_TestCase
{
    public function testFormatError()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\IdFromObject();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Formatter::class, $formatter);
        $this->assertFalse($formatter->hasError());
        $ret = $formatter->format(new \stdClass());
        $this->assertTrue($formatter->hasError());
        $this->assertNull($ret);
        $this->assertSame("La classe n'est pas convertible", $formatter->getErrorMessage(new \stdClass()));
    }

    public function testFormatSuccess()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\IdFromObject();
        $obj = $this->getMockBuilder(\FMUP\Import\Config\Field\Formatter\Interfaces\ObjectWithId::class)
            ->setMethods(array('getId'))
            ->getMock();
        $obj->method('getId')->willReturn(1);
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Formatter::class, $formatter);
        $this->assertFalse($formatter->hasError());
        $ret = $formatter->format($obj);
        $this->assertFalse($formatter->hasError());
        $this->assertSame(1, $ret);
    }
}
