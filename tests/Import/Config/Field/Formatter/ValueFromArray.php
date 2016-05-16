<?php
namespace Tests\Import\Config\Field\Formatter;


class ValueFromArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatErrorWhenNoArrayDefined()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\ValueFromArray();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Formatter::class, $formatter);
        $this->assertFalse($formatter->hasError());
        $this->expectException(\FMUP\Exception\UnexpectedValue::class);
        $this->expectExceptionMessage("No array defined");
        $formatter->format('');
        $this->assertTrue($formatter->hasError());
    }
}
