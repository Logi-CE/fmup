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
        $config = $this->getMock(\FMUP\Import\Config::class);
        $db = $this->getMock(\FMUP\Db::class, array('beginTransaction', 'commit', 'rollback'));
        $db->expects($this->once())->method('beginTransaction');
        $db->expects($this->once())->method('rollback');
        $db->expects($this->never())->method('commit');
        $validIterator = $this->getMock(\FMUP\Import\Iterator\ValidatorIterator::class, array('rewind'), array(new \ArrayIterator(array())));
        $validIterator->expects($this->once())->method('rewind')->willThrowException(new \Exception('erreur test'));
        $launch = $this->getMock(
            \FMUP\Import\Launch::class,
            array('getDb', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator'),
            array(__FILE__, $config)
        );
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
        $config = $this->getMock(\FMUP\Import\Config::class, array('getDoublonLigne', 'insertLine'));
        $config->expects($this->exactly(3))->method('getDoublonLigne')->willReturnOnConsecutiveCalls(true, false, true);
        $config->expects($this->once())->method('insertLine');
        $db = $this->getMock(\FMUP\Db::class, array('beginTransaction', 'commit', 'rollback'));
        $db->expects($this->once())->method('beginTransaction');
        $db->expects($this->never())->method('rollback');
        $db->expects($this->once())->method('commit');
        $validIterator = $this->getMock(
            \FMUP\Import\Iterator\ValidatorIterator::class,
            array('getTotalErrors', 'getTotalInsert', 'getTotalUpdate', 'current', 'next', 'getValid', 'valid'),
            array(new \ArrayIterator(array()))
        );
        $validIterator->method('current')->willReturnOnConsecutiveCalls($config, $config, $config);
        $validIterator->method('valid')->willReturnOnConsecutiveCalls(true, true, true, false);
        $validIterator->method('getValid')->willReturn(true);
        $validIterator->method('getTotalErrors')->willReturn(1);
        $validIterator->method('getTotalInsert')->willReturn(2);
        $validIterator->method('getTotalUpdate')->willReturn(3);
        $launch = $this->getMock(
            \FMUP\Import\Launch::class,
            array('getDb', 'getLineToConfigIterator', 'getDoublonIterator', 'getValidatorIterator'),
            array(__FILE__, $config)
        );
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
