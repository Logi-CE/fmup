<?php
/**
 * DateSQL.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter\DateSQL;

class DateSQLTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $formatter = $this->getMockBuilder('\FMUP\Import\Config\Field\Formatter\DateSQL')
            ->setMethods(array('toDate'))
            ->getMock();
        /** @var $formatter DateSQL */
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Formatter', $formatter);
        $this->assertFalse($formatter->hasError());
        $this->assertSame('Champ vide', $formatter->format(''));
        $this->assertTrue($formatter->hasError());
        $this->assertSame("La valeur  n'est pas convertible", $formatter->getErrorMessage());

        $formatter2 = $this->getMockBuilder('\FMUP\Import\Config\Field\Formatter\DateSQL')
            ->setMethods(array('toDate'))
            ->getMock();
        $formatter2->method('toDate')->willReturnOnConsecutiveCalls('2010-10-10 10:10:10', false)->with($this->equalTo('test'));
        /** @var $formatter2 DateSQL */
        $this->assertFalse($formatter2->hasError());
        $this->assertSame('2010-10-10 10:10:10', $formatter2->format('test'));
        $this->assertFalse($formatter2->hasError());
        $this->assertSame('test', $formatter2->format('test'));
        $this->assertTrue($formatter2->hasError());
        $this->assertSame("La valeur test n'est pas convertible", $formatter->getErrorMessage('test'));

        $formatter3 = $this->getMockBuilder('\FMUP\Import\Config\Field\Formatter\DateSQL')
            ->setMethods(array('toDate'))
            ->setConstructorArgs(array(DateSQL::DATE))
            ->getMock();
        $formatter3->method('toDate')->willReturnOnConsecutiveCalls('2010-10-10', false)->with($this->equalTo('test'));
        /** @var $formatter3 DateSQL */
        $this->assertFalse($formatter3->hasError());
        $this->assertSame('2010-10-10', $formatter3->format('test'));
        $this->assertFalse($formatter3->hasError());
        $this->assertSame('test', $formatter3->format('test'));
        $this->assertTrue($formatter3->hasError());
        $this->assertSame("La valeur test n'est pas convertible", $formatter->getErrorMessage('test'));
    }

    public function testToDate()
    {
        $formatter = new DateSQL;
        $formatterDate = new DateSQL(DateSQL::DATE);
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Formatter', $formatter);
        $function = new \ReflectionMethod('\FMUP\Import\Config\Field\Formatter\DateSQL', 'toDate');
        $function->setAccessible(true);
        $this->assertRegExp('~^2012-11-10 [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$~', $function->invoke($formatter, '10/11/2012'));
        $this->assertRegExp('~^2010-11-12 [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$~', $function->invoke($formatter, '2010-11-12'));
        $this->assertSame('2012-11-10', $function->invoke($formatterDate, '10/11/2012'));
        $this->assertSame('2010-11-12', $function->invoke($formatterDate, '2010-11-12'));
    }


}
