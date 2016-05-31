<?php
/**
 * Config.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import;

class ModelMockImportConfig
{
}

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testAddGetField()
    {
        $field = $this->getMock(\FMUP\Import\Config\Field::class, array('getRequired', 'addValidator'), array(), '', false);
        $field->method('getRequired')->willReturnOnConsecutiveCalls(false, true);
        $field->expects($this->once())->method('addValidator')->with($this->equalTo(new \FMUP\Import\Config\Field\Validator\Required()));
        /** @var $field \FMUP\Import\Config\Field */
        $config = new \FMUP\Import\Config();
        $this->assertSame($config, $config->addField($field));
        $field2 = clone $field;
        $this->assertSame($config, $config->addField($field2));
        $this->assertEquals(array($field, $field2), $config->getListeField());
        $this->assertEquals($field2, $config->getField(1));
    }

    public function testAddGetConfigObjet()
    {
        $configObject = $this->getMock(\FMUP\Import\Config\ConfigObjet::class, array(), array(), '', false);
        /** @var $configObject \FMUP\Import\Config\ConfigObjet */
        $config = new \FMUP\Import\Config();
        $this->assertSame($config, $config->addConfigObjet($configObject));
        $configObject2 = clone $configObject;
        $this->assertSame($config, $config->addConfigObjet($configObject2));
        $this->assertEquals(array($configObject, $configObject2), $config->getListeConfigObjet());
    }

    public function testSetGetDoublonLigne()
    {
        $config = new \FMUP\Import\Config();
        $this->assertSame(0, $config->getDoublonLigne());
        $this->assertSame($config, $config->setDoublonLigne(10));
        $this->assertSame(10, $config->getDoublonLigne());
    }

    public function testGetErrors()
    {
        $config = new \FMUP\Import\Config();
        $this->assertTrue(is_array($config->getErrors()));
    }

    public function testValidateLine()
    {
        $yes = $this->getMock(\FMUP\Import\Config\Field::class, array('validateField'), array(), '', false);
        $yes->expects($this->once())->method('validateField')->willReturn(true);
        $no = $this->getMock(\FMUP\Import\Config\Field::class, array('validateField', 'getName'), array(), '', false);
        $no->expects($this->once())->method('validateField')->willReturn(false);
        $no->expects($this->once())->method('getName')->willReturn('no');
        $fieldList = array($yes, $no);
        $config = $this->getMock(\FMUP\Import\Config::class, array('getListeField', 'validateObjects'));
        $config->expects($this->exactly(2))->method('getListeField')->willReturnOnConsecutiveCalls(array(), $fieldList);
        $config->expects($this->once())->method('validateObjects');
        /** @var $config \FMUP\Import\Config */
        $this->assertTrue($config->validateLine());
        $this->assertFalse($config->validateLine());
        $this->assertSame(array('no' => 'non valide'), $config->getErrors());
    }

    public function testValidateObjects()
    {
        $where = array(
            'champCible' => "champCible LIKE '%value%'",
        );
        $mockModel = $this->getMock(\stdClass::class, array('setAttribute', 'findFirst'));
        $mockModel->expects($this->exactly(6))->method('setAttribute')
            ->willReturn($mockModel)
            ->with($this->equalTo('champCible'), $this->equalTo('value'));
        $mockModel->method('findFirst')->with($this->equalTo($where))->willReturnOnConsecutiveCalls(false, true, false);
        $field = $this->getMock(
            \FMUP\Import\Config\Field::class,
            array('getChampCible', 'getValue'),
            array(),
            '',
            false
        );
        $field->method('getChampCible')->willReturn('champCible');
        $field->method('getValue')->willReturn('value');
        $configObj = $this->getMock(
            \FMUP\Import\Config\ConfigObjet::class,
            array(
                'getNomObjet', 'getListeIndexChamp', 'setStatutInsertion', 'setStatutMaj', 'getPriorite'
            ),
            array(),
            '',
            false
        );
        $configObj->expects($this->exactly(2))->method('setStatutInsertion');
        $configObj->expects($this->once())->method('setStatutMaj');
        $configObj->method('getListeIndexChamp')->willReturn(array($field, $field));
        $configObj->method('getNomObjet')->willReturn(ModelMockImportConfig::class);
        $config = $this->getMock(\FMUP\Import\Config::class, array('getListeConfigObjet', 'createNomObject', 'usort', 'getField'));
        $config->method('getListeConfigObjet')->willReturn(array($configObj, $configObj, $configObj));
        $config->expects($this->exactly(3))
            ->method('createNomObject')
            ->willReturn($mockModel)
            ->with($this->equalTo(ModelMockImportConfig::class));
        $config->method('getField')->willReturn($field);
        /** @var $config \FMUP\Import\Config */
        $this->assertSame($config, $config->validateObjects());
    }

    public function testInsertLine()
    {
        $instanceFound = $this->getMock(\stdClass::class, array('getId'));
        $instanceFound->method('getId')->willReturn(2);
        $mockModel = $this->getMock(\stdClass::class, array('setAttribute', 'findFirst', 'save'));
        $mockModel->method('setAttribute')->willReturn($mockModel);
        $mockModel->method('findFirst')->willReturnOnConsecutiveCalls(false, $instanceFound, false);
        $mockModel->method('save')->willReturn(true);
        $field = $this->getMock(
            \FMUP\Import\Config\Field::class,
            array('getChampCible', 'getValue'),
            array(),
            '',
            false
        );
        $field->method('getChampCible')->willReturn('champCible');
        $field->method('getValue')->willReturn('value');

        $field2 = $this->getMock(
            \FMUP\Import\Config\Field::class,
            array('getTableCible', 'getValue'),
            array(),
            '',
            false
        );
        $field2->method('getTableCible')->willReturn(ModelMockImportConfig::class);
        $field2->method('getValue')->willReturn('value');
        $configObj = $this->getMock(
            \FMUP\Import\Config\ConfigObjet::class,
            array(
                'getNomObjet',
                'getListeIndexChamp',
                'setStatutInsertion',
                'setStatutMaj',
                'getPriorite',
                'getIdNecessaire',
                'getNomAttribut'
            ),
            array(),
            '',
            false
        );
        $configObj->method('getListeIndexChamp')->willReturn(array($field, $field));
        $configObj->method('getNomObjet')->willReturn(ModelMockImportConfig::class);
        $configObj->method('getIdNecessaire')->willReturn(array(ModelMockImportConfig::class));
        $configObj->method('getNomAttribut')->willReturn(array(ModelMockImportConfig::class => 'nom'));
        $config = $this->getMock(
            \FMUP\Import\Config::class,
            array('getListeConfigObjet', 'createNomObject', 'usort', 'getField', 'getListeField')
        );
        $config->method('getListeConfigObjet')->willReturn(array($configObj, $configObj, $configObj));
        $config->method('getListeField')->willReturn(array($field2, $field2));
        $config->expects($this->exactly(3))
            ->method('createNomObject')
            ->willReturn($mockModel)
            ->with($this->equalTo(ModelMockImportConfig::class));
        $config->method('getField')->willReturn($field);
        /** @var $config \FMUP\Import\Config */
        $this->assertSame($config, $config->insertLine());
    }

    public function testInsertLineFailsOnSave()
    {
        $instanceFound = $this->getMock(\stdClass::class, array('getId'));
        $instanceFound->method('getId')->willReturn(2);
        $mockModel = $this->getMock(\stdClass::class, array('findFirst', 'save', 'getErrors'));
        $mockModel->method('findFirst')->willReturn(false);
        $mockModel->method('save')->willReturn(false);
        $mockModel->method('getErrors')->willReturn(array('error 1', 'error 2'));
        $configObj = $this->getMock(
            \FMUP\Import\Config\ConfigObjet::class,
            array(
                'getNomObjet',
            ),
            array(),
            '',
            false
        );
        $configObj->method('getNomObjet')->willReturn(ModelMockImportConfig::class);
        $config = $this->getMock(
            \FMUP\Import\Config::class,
            array('getListeConfigObjet', 'createNomObject', 'usort')
        );
        $config->method('getListeConfigObjet')->willReturn(array($configObj));
        $config->expects($this->once())
            ->method('createNomObject')
            ->willReturn($mockModel)
            ->with($this->equalTo(ModelMockImportConfig::class));
        /** @var $config \FMUP\Import\Config */
        $this->expectException(\FMUP\Import\Exception::class);
        $this->expectExceptionMessage("error 1;error 2");
        $config->insertLine();
    }

    public function testToString()
    {
        $field = $this->getMock(
            \FMUP\Import\Config\Field::class,
            array('getName', 'getValue'),
            array(),
            '',
            false
        );
        $field->method('getName')->willReturn('name');
        $field->method('getValue')->willReturn('value');
        $config = $this->getMock(\FMUP\Import\Config::class, array('getListeField'));
        $config->method('getListeField')->willReturn(array($field, $field));
        /** @var $config \FMUP\Import\Config */
        $this->assertSame("0 \tname \tvalue \t\n1 \tname \tvalue \t\n", (string)$config);
    }

    public function testSortByPriority()
    {
        $method = new \ReflectionMethod(\FMUP\Import\Config::class, 'sortByPriority');
        $method->setAccessible(true);
        $config = new \FMUP\Import\Config;
        $comparingA = $this->getMock(\stdClass::class, array('getPriorite'));
        $comparingA->method('getPriorite')->willReturnOnConsecutiveCalls(1, 2, 2, 3, 3);
        $comparingB = $this->getMock(\stdClass::class, array('getPriorite'));
        $comparingB->method('getPriorite')->willReturnOnConsecutiveCalls(1, 3, 3, 2, 2);
        $this->assertSame(0, $method->invoke($config, $comparingA, $comparingB));
        $this->assertSame(-1, $method->invoke($config, $comparingA, $comparingB));
        $this->assertSame(1, $method->invoke($config, $comparingA, $comparingB));
    }
}
