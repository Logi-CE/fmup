<?php
/**
 * Display.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import;

use FMUP\Import\Config;

class DisplayTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $config = $this->getMock(\FMUP\Import\Config::class, array('getDoublonLigne', 'insertLine'));
        $validIterator = $this->getMock(
            \FMUP\Import\Iterator\ValidatorIterator::class,
            array('getTotalErrors', 'getTotalInsert', 'getTotalUpdate', 'current', 'next', 'valid', 'key'),
            array(new \ArrayIterator(array()))
        );
        $validIterator->method('current')->willReturnOnConsecutiveCalls($config, $config, $config);
        $validIterator->method('valid')->willReturnOnConsecutiveCalls(true, true, true, false);
        $validIterator->method('key')->willReturnOnConsecutiveCalls(0, 1, 2, 3, 4);
        $validIterator->method('getTotalErrors')->willReturn(1);
        $validIterator->method('getTotalInsert')->willReturn(2);
        $validIterator->method('getTotalUpdate')->willReturn(3);
        $display = $this->getMock(
            \FMUP\Import\Display::class,
            array('displayImport', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator'),
            array(__FILE__, $config)
        );
        $display->expects($this->once())->method('getLineToConfigIterator')->with(
            $this->equalTo(new \FMUP\Import\Iterator\FileIterator(__FILE__)),
            $this->equalTo($config)
        )
            ->willReturn($this->getMock(\FMUP\Import\Iterator\LineToConfigIterator::class, array(), array(), '', false));
        $display->expects($this->once())->method('getDoublonIterator')
            ->willReturn($this->getMock(\FMUP\Import\Iterator\DuplicateIterator::class, array(), array(), '', false));
        $display->expects($this->once())->method('getValidatorIterator')->willReturn($validIterator);
        /** @var $display \FMUP\Import\Launch */
        $display->parse();
        $this->assertSame(1, $display->getTotalErrors());
        $this->assertSame(2, $display->getTotalInsert());
        $this->assertSame(3, $display->getTotalUpdate());
    }

    public function testParseOnFail()
    {
        $config = $this->getMock(\FMUP\Import\Config::class, array('getDoublonLigne', 'insertLine'));
        $validIterator = $this->getMock(
            \FMUP\Import\Iterator\ValidatorIterator::class,
            array('rewind', 'getTotalErrors', 'getTotalInsert', 'getTotalUpdate', 'current', 'next', 'valid', 'key'),
            array(new \ArrayIterator(array()))
        );
        $validIterator->method('rewind')->willThrowException(new \Exception('test message error'));
        $validIterator->method('current')->willReturnOnConsecutiveCalls($config, $config, $config);
        $validIterator->method('valid')->willReturnOnConsecutiveCalls(true, true, true, false);
        $validIterator->method('key')->willReturnOnConsecutiveCalls(0, 1, 2, 3, 4);
        $validIterator->method('getTotalErrors')->willReturn(1);
        $validIterator->method('getTotalInsert')->willReturn(2);
        $validIterator->method('getTotalUpdate')->willReturn(3);
        $display = $this->getMock(
            \FMUP\Import\Display::class,
            array('displayImport', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator'),
            array(__FILE__, $config)
        );
        $display->expects($this->once())->method('getLineToConfigIterator')->with(
            $this->equalTo(new \FMUP\Import\Iterator\FileIterator(__FILE__)),
            $this->equalTo($config)
        )
            ->willReturn($this->getMock(\FMUP\Import\Iterator\LineToConfigIterator::class, array(), array(), '', false));
        $display->expects($this->once())->method('getDoublonIterator')
            ->willReturn($this->getMock(\FMUP\Import\Iterator\DuplicateIterator::class, array(), array(), '', false));
        $display->expects($this->once())->method('getValidatorIterator')->willReturn($validIterator);
        /** @var $display \FMUP\Import\Launch */
        $this->expectOutputString('test message error');
        $display->parse();
    }
}
