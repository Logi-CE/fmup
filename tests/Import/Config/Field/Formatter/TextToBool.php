<?php
/**
 * TextToBool.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Config\Field\Formatter;


class TextToBoolTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatErrorWhenEmpty()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\TextToBool();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Formatter::class, $formatter);
        $this->assertFalse($formatter->hasError());
        $this->assertSame('', $formatter->format(''));
        $this->assertTrue($formatter->hasError());
        $this->assertSame("La valeur  n'est pas convertible", $formatter->getErrorMessage(''));
    }

    public function testFormatErrorWhenNotAllowedValue()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\TextToBool();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Formatter::class, $formatter);
        $this->assertFalse($formatter->hasError());
        $this->assertSame('', $formatter->format('yes'));
        $this->assertFalse($formatter->hasError());
        $this->assertSame("La valeur yes n'est pas convertible", $formatter->getErrorMessage('yes'));
    }

    public function testFormatWhenValueAreOk()
    {
        $formatter = new \FMUP\Import\Config\Field\Formatter\TextToBool();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Formatter::class, $formatter);
        $this->assertFalse($formatter->hasError());
        $this->assertTrue($formatter->format('oui'));
        $this->assertFalse($formatter->hasError());
        $this->assertTrue($formatter->format('Oui'));
        $this->assertFalse($formatter->hasError());
        $this->assertTrue($formatter->format('OuI'));
        $this->assertFalse($formatter->hasError());
        $this->assertTrue($formatter->format('OUI'));
        $this->assertFalse($formatter->hasError());
        $this->assertTrue($formatter->format('oUI'));
        $this->assertFalse($formatter->hasError());
        $this->assertFalse($formatter->format('non'));
        $this->assertFalse($formatter->hasError());
        $this->assertFalse($formatter->format('Non'));
        $this->assertFalse($formatter->hasError());
        $this->assertFalse($formatter->format('NoN'));
        $this->assertFalse($formatter->hasError());
        $this->assertFalse($formatter->format('NON'));
        $this->assertFalse($formatter->hasError());
        $this->assertFalse($formatter->format('nON'));
    }
}
