<?php
/**
 * Launch.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import;


class LaunchTest extends \PHPUnit_Framework_TestCase
{
    public function testParseFailWhenErrorOccurs()
    {
        $config = $this->getMockBuilder('\FMUP\Import\Config')->getMock();
        $db = $this->getMockBuilder('\FMUP\Db')->setMethods(array('beginTransaction', 'commit', 'rollback'))->getMock();
        $db->expects($this->once())->method('beginTransaction');
        $db->expects($this->once())->method('rollback');
        $db->expects($this->never())->method('commit');
        $validIterator = $this->getMockBuilder('\FMUP\Import\Iterator\ValidatorIterator')
            ->setMethods(array('rewind'))
            ->setConstructorArgs(array(new \ArrayIterator(array())))
            ->getMock();
        $validIterator->expects($this->once())->method('rewind')->willThrowException(new \Exception('erreur test'));
        $launch = $this->getMockBuilder('\FMUP\Import\Launch')
            ->setMethods(array('getDb', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator'))
            ->setConstructorArgs(array(__FILE__, $config))
            ->getMock();
        $launch->method('getDb')->willReturn($db);
        $launch->expects($this->once())->method('getLineToConfigIterator')->with(
            $this->equalTo(new \FMUP\Import\Iterator\FileIterator(__FILE__)),
            $this->equalTo($config)
        )
        ->willReturn(new \ArrayIterator(array()));
        $launch->expects($this->once())->method('getDoublonIterator')->willReturn(new \ArrayIterator(array()));
        $launch->expects($this->once())->method('getValidatorIterator')->willReturn($validIterator);
        /** @var $launch \FMUP\Import\Launch */
        $this->expectOutputString('Une erreur a été détecté lors de l\'import.' . PHP_EOL . 'erreur test');
        $launch->parse();
    }

    public function testParse()
    {
        $config = $this->getMockBuilder('\FMUP\Import\Config')
            ->setMethods(array('getDoublonLigne', 'insertLine'))
            ->getMock();
        $config->expects($this->exactly(3))->method('getDoublonLigne')->willReturnOnConsecutiveCalls(true, false, true);
        $config->expects($this->once())->method('insertLine');
        $db = $this->getMockBuilder('\FMUP\Db')
            ->setMethods(array('beginTransaction', 'commit', 'rollback'))
            ->getMock();
        $db->expects($this->once())->method('beginTransaction');
        $db->expects($this->never())->method('rollback');
        $db->expects($this->once())->method('commit');
        $validIterator = $this->getMockBuilder('\FMUP\Import\Iterator\ValidatorIterator')
            ->setMethods(array('getTotalErrors', 'getTotalInsert', 'getTotalUpdate', 'current', 'next', 'getValid', 'valid'))
            ->setConstructorArgs(array(new \ArrayIterator(array())))
            ->getMock();
        $validIterator->method('current')->willReturnOnConsecutiveCalls($config, $config, $config);
        $validIterator->method('valid')->willReturnOnConsecutiveCalls(true, true, true, false);
        $validIterator->method('getValid')->willReturn(true);
        $validIterator->method('getTotalErrors')->willReturn(1);
        $validIterator->method('getTotalInsert')->willReturn(2);
        $validIterator->method('getTotalUpdate')->willReturn(3);
        $launch = $this->getMockBuilder('\FMUP\Import\Launch')
            ->setMethods(array('getDb', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator'))
            ->setConstructorArgs(array(__FILE__, $config))
            ->getMock();
        $launch->method('getDb')->willReturn($db);
        $launch->expects($this->once())->method('getLineToConfigIterator')->with(
            $this->equalTo(new \FMUP\Import\Iterator\FileIterator(__FILE__)),
            $this->equalTo($config)
        )
            ->willReturn(new \ArrayIterator(array()));
        $launch->expects($this->once())->method('getDoublonIterator')->willReturn(new \ArrayIterator(array()));
        $launch->expects($this->once())->method('getValidatorIterator')->willReturn($validIterator);
        /** @var $launch \FMUP\Import\Launch */
        $this->expectOutputString('Import terminé.' . PHP_EOL);
        $launch->parse();
        $this->assertSame(1, $launch->getTotalErrors());
        $this->assertSame(2, $launch->getTotalInsert());
        $this->assertSame(3, $launch->getTotalUpdate());
    }
}
