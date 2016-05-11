<?php
/**
 * IdFromField.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Formatter;


class IdFromFieldTest extends \PHPUnit_Framework_TestCase
{
    public function testFormatterErrorWhenNotExists()
    {
        $db = $this->getMock(\FMUP\Db::class, array('fetchRow'), array(), '', false);
        $db->expects($this->once())
            ->method('fetchRow')
            ->with($this->equalTo("SELECT id FROM originTable WHERE originField LIKE '%test%'"))
            ->willReturn(null);
        $formatter = $this->getMock(
            \FMUP\Import\Config\Field\Formatter\IdFromField::class,
            array('getDb'),
            array('originField', 'originTable')
        );
        $formatter->method('getDb')->willReturn($db);
        /** @var $formatter \FMUP\Import\Config\Field\Formatter\IdFromField */
        $this->assertSame('test', $formatter->format('test'));
        $this->assertTrue($formatter->hasError());
        $this->assertSame(
            'Aucune correspondance n\'a été trouvé pour le champ : '.
                "'originField' de la table 'originTable' pour la valeur : 'test'",
            $formatter->getErrorMessage('test')
        );
    }

    public function testFormatterErrorWhenEmpty()
    {
        $db = $this->getMock(\FMUP\Db::class, array('fetchRow'), array(), '', false);
        $db->expects($this->never())->method('fetchRow');
        $formatter = $this->getMock(
            \FMUP\Import\Config\Field\Formatter\IdFromField::class,
            array('getDb'),
            array('originField', 'originTable')
        );
        $formatter->method('getDb')->willReturn($db);
        /** @var $formatter \FMUP\Import\Config\Field\Formatter\IdFromField */
        $this->assertSame('', $formatter->format(''));
        $this->assertTrue($formatter->hasError());
        $this->assertSame(
            'Aucune correspondance n\'a été trouvé pour le champ : '.
            "'originField' de la table 'originTable' pour la valeur : ''",
            $formatter->getErrorMessage('')
        );
    }

    public function testFormatterSuccess()
    {
        $db = $this->getMock(\FMUP\Db::class, array('fetchRow'), array(), '', false);
        $db->method('fetchRow')
            ->with($this->equalTo("SELECT id FROM originTable WHERE originField LIKE '%test%'"))
            ->willReturn(array('id' => 10));
        $formatter = $this->getMock(
            \FMUP\Import\Config\Field\Formatter\IdFromField::class,
            array('getDb'),
            array('originField', 'originTable')
        );
        $formatter->method('getDb')->willReturn($db);
        /** @var $formatter \FMUP\Import\Config\Field\Formatter\IdFromField */
        $this->assertSame(10, $formatter->format('test'));
        $this->assertFalse($formatter->hasError());
    }

    public function testSetGetDb()
    {
        $db = $this->getMock(\FMUP\Db::class, array(), array(), '', false);
        $db2 = clone $db;
        $formatter = $this->getMock(
            \FMUP\Import\Config\Field\Formatter\IdFromField::class,
            array('getModelDb'),
            array('originField', 'originTable')
        );
        $formatter->method('getModelDb')->willReturn($db);
        /** @var $formatter \FMUP\Import\Config\Field\Formatter\IdFromField */
        /** @var $db2 \FMUP\Db */
        $this->assertSame($db, $formatter->getDb());
        $this->assertSame($formatter, $formatter->setDb($db2));
        $this->assertSame($db2, $formatter->getDb());
    }
}
