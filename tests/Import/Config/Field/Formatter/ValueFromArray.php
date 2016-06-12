<?php
namespace Tests\Import\Config\Field\Formatter;


class ValueFromArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatErrorWhenNoArrayDefined()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\ValueFromArray();
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Formatter', $formatter);
        $this->assertFalse($formatter->hasError());
        $this->setExpectedException('\FMUP\Exception\UnexpectedValue', "No array defined");
        $formatter->format('');
        $this->assertTrue($formatter->hasError());
        $this->assertSame("La valeur  n'a pas été trouvée dans le tableau", $formatter->getErrorMessage(''));
    }

    public function testFormatErrorWhenNotDefinedValue()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\ValueFromArray();
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Formatter', $formatter);
        $this->assertFalse($formatter->hasError());
        $arr = array(
            'A' => 1,
            'B' => 2,
            'C' => 3,
        );
        $ret = $formatter->setArray($arr);
        $this->assertSame($formatter, $ret);
        $resFormat = $formatter->format('D');
        $this->assertTrue($formatter->hasError());
        $this->assertNull($resFormat);
        $this->assertSame("La valeur D n'a pas été trouvée dans le tableau", $formatter->getErrorMessage('D'));
    }

    public function testFormatWhenSuccess()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\ValueFromArray();
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Formatter', $formatter);
        $this->assertFalse($formatter->hasError());
        $arr = array(
            'A' => 1,
            'B' => 2,
            'C' => 3,
        );
        $ret = $formatter->setArray($arr);
        $this->assertSame($formatter, $ret);
        $resFormat = $formatter->format('A');
        $this->assertFalse($formatter->hasError());
        $this->assertSame(1, $resFormat);
    }
}
