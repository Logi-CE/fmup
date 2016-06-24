<?php
/**
 * Display.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import;

use FMUP\Import\Config;

class DisplayTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)
            ->setMethods(array('getDoublonLigne', 'insertLine'))
            ->getMock();
        $validIterator = $this->getMockBuilder(\FMUP\Import\Iterator\ValidatorIterator::class)
            ->setMethods(
                array('getTotalErrors', 'getTotalInsert', 'getTotalUpdate', 'current', 'next', 'valid', 'key')
            )
            ->setConstructorArgs(array(new \ArrayIterator(array())))
            ->getMock();
        $validIterator->method('current')->willReturnOnConsecutiveCalls($config, $config, $config);
        $validIterator->method('valid')->willReturnOnConsecutiveCalls(true, true, true, false);
        $validIterator->method('key')->willReturnOnConsecutiveCalls(0, 1, 2, 3, 4);
        $validIterator->method('getTotalErrors')->willReturn(1);
        $validIterator->method('getTotalInsert')->willReturn(2);
        $validIterator->method('getTotalUpdate')->willReturn(3);
        $display = $this->getMockBuilder(\FMUP\Import\Display::class)
            ->setMethods(array('displayImport', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator'))
            ->setConstructorArgs(array(__FILE__, $config))
            ->getMock();
        $display->expects($this->once())
            ->method('getLineToConfigIterator')
            ->with(
                $this->equalTo(new \FMUP\Import\Iterator\FileIterator(__FILE__)),
                $this->equalTo($config)
            )
            ->willReturn(
                $this->getMockBuilder(\FMUP\Import\Iterator\LineToConfigIterator::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $display->expects($this->once())
            ->method('getDoublonIterator')
            ->willReturn(
                $this->getMockBuilder(\FMUP\Import\Iterator\DuplicateIterator::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $display->expects($this->once())->method('getValidatorIterator')->willReturn($validIterator);
        /** @var $display \FMUP\Import\Launch */
        $display->parse();
        $this->assertSame(1, $display->getTotalErrors());
        $this->assertSame(2, $display->getTotalInsert());
        $this->assertSame(3, $display->getTotalUpdate());
    }

    public function testParseOnFail()
    {
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)
            ->setMethods(array('getDoublonLigne', 'insertLine'))
            ->getMock();
        $validIterator = $this->getMockBuilder(\FMUP\Import\Iterator\ValidatorIterator::class)
            ->setMethods(
                array('rewind', 'getTotalErrors', 'getTotalInsert', 'getTotalUpdate', 'current', 'next', 'valid', 'key')
            )
            ->setConstructorArgs(array(new \ArrayIterator(array())))
            ->getMock();
        $validIterator->method('rewind')->willThrowException(new \Exception('test message error'));
        $validIterator->method('current')->willReturnOnConsecutiveCalls($config, $config, $config);
        $validIterator->method('valid')->willReturnOnConsecutiveCalls(true, true, true, false);
        $validIterator->method('key')->willReturnOnConsecutiveCalls(0, 1, 2, 3, 4);
        $validIterator->method('getTotalErrors')->willReturn(1);
        $validIterator->method('getTotalInsert')->willReturn(2);
        $validIterator->method('getTotalUpdate')->willReturn(3);
        $display = $this->getMockBuilder(\FMUP\Import\Display::class)
            ->setMethods(
                array('displayImport', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator')
            )
            ->setConstructorArgs(array(__FILE__, $config))
            ->getMock();
        $display->expects($this->once())
            ->method('getLineToConfigIterator')
            ->with(
                $this->equalTo(new \FMUP\Import\Iterator\FileIterator(__FILE__)),
                $this->equalTo($config)
            )
            ->willReturn(
                $this->getMockBuilder(\FMUP\Import\Iterator\LineToConfigIterator::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $display->expects($this->once())
            ->method('getDoublonIterator')
            ->willReturn(
                $this->getMockBuilder(\FMUP\Import\Iterator\DuplicateIterator::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $display->expects($this->once())->method('getValidatorIterator')->willReturn($validIterator);
        /** @var $display \FMUP\Import\Launch */
        $this->expectOutputString('test message error');
        $display->parse();
    }
}
