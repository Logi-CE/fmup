<?php
/**
 * Config.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import;

class ModelMockImportConfig
{
}

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testAddGetField()
    {
        $field = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('getRequired', 'addValidator'))
            ->disableOriginalConstructor()
            ->getMock();
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
        $configObject = $this->getMockBuilder(\FMUP\Import\Config\ConfigObjet::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $yes = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('validateField'))
            ->disableOriginalConstructor()
            ->getMock();
        $yes->expects($this->once())->method('validateField')->willReturn(true);
        $no = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('validateField', 'getName'))
            ->disableOriginalConstructor()
            ->getMock();
        $no->expects($this->once())->method('validateField')->willReturn(false);
        $no->expects($this->once())->method('getName')->willReturn('no');
        $fieldList = array($yes, $no);
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)
            ->setMethods(array('getListeField', 'validateObjects'))
            ->getMock();
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
        $mockModel = $this->getMockBuilder(\stdClass::class)->setMethods(array('setAttribute', 'findFirst'))->getMock();
        $mockModel->expects($this->exactly(6))->method('setAttribute')
            ->willReturn($mockModel)
            ->with($this->equalTo('champCible'), $this->equalTo('value'));
        $mockModel->method('findFirst')->with($this->equalTo($where))->willReturnOnConsecutiveCalls(false, true, false);
        $field = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('getChampCible', 'getValue'))
            ->disableOriginalConstructor()
            ->getMock();
        $field->method('getChampCible')->willReturn('champCible');
        $field->method('getValue')->willReturn('value');
        $configObj = $this->getMockBuilder(\FMUP\Import\Config\ConfigObjet::class)
            ->setMethods(
                array(
                    'getNomObjet', 'getListeIndexChamp', 'setStatutInsertion', 'setStatutMaj', 'getPriorite'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();
        $configObj->expects($this->exactly(2))->method('setStatutInsertion');
        $configObj->expects($this->once())->method('setStatutMaj');
        $configObj->method('getListeIndexChamp')->willReturn(array($field, $field));
        $configObj->method('getNomObjet')->willReturn(ModelMockImportConfig::class);
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)
            ->setMethods(array('getListeConfigObjet', 'createNomObject', 'usort', 'getField'))
            ->getMock();
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
        $instanceFound = $this->getMockBuilder(\stdClass::class)->setMethods(array('getId'))->getMock();
        $instanceFound->method('getId')->willReturn(2);
        $mockModel = $this->getMockBuilder(\stdClass::class)
            ->setMethods(array('setAttribute', 'findFirst', 'save'))
            ->getMock();
        $mockModel->method('setAttribute')->willReturn($mockModel);
        $mockModel->method('findFirst')->willReturnOnConsecutiveCalls(false, $instanceFound, false);
        $mockModel->method('save')->willReturn(true);
        $field = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('getChampCible', 'getValue'))
            ->disableOriginalConstructor()
            ->getMock();
        $field->method('getChampCible')->willReturn('champCible');
        $field->method('getValue')->willReturn('value');

        $field2 = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('getTableCible', 'getValue'))
            ->disableOriginalConstructor()
            ->getMock();
        $field2->method('getTableCible')->willReturn(ModelMockImportConfig::class);
        $field2->method('getValue')->willReturn('value');
        $configObj = $this->getMockBuilder(\FMUP\Import\Config\ConfigObjet::class)
            ->setMethods(
                array(
                    'getNomObjet',
                    'getListeIndexChamp',
                    'setStatutInsertion',
                    'setStatutMaj',
                    'getPriorite',
                    'getIdNecessaire',
                    'getNomAttribut'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();
        $configObj->method('getListeIndexChamp')->willReturn(array($field, $field));
        $configObj->method('getNomObjet')->willReturn(ModelMockImportConfig::class);
        $configObj->method('getIdNecessaire')->willReturn(array(ModelMockImportConfig::class));
        $configObj->method('getNomAttribut')->willReturn(array(ModelMockImportConfig::class => 'nom'));
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)
            ->setMethods(
                array('getListeConfigObjet', 'createNomObject', 'usort', 'getField', 'getListeField')
            )
            ->getMock();
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
        $instanceFound = $this->getMockBuilder(\stdClass::class)->setMethods(array('getId'))->getMock();
        $instanceFound->method('getId')->willReturn(2);
        $mockModel = $this->getMockBuilder(\stdClass::class)->setMethods(array('findFirst', 'save', 'getErrors'))->getMock();
        $mockModel->method('findFirst')->willReturn(false);
        $mockModel->method('save')->willReturn(false);
        $mockModel->method('getErrors')->willReturn(array('error 1', 'error 2'));
        $configObj = $this->getMockBuilder(\FMUP\Import\Config\ConfigObjet::class)
            ->setMethods(array('getNomObjet'))
            ->disableOriginalConstructor()
            ->getMock();
        $configObj->method('getNomObjet')->willReturn(ModelMockImportConfig::class);
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)
            ->setMethods(array('getListeConfigObjet', 'createNomObject', 'usort'))
            ->getMock();
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
        $field = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('getName', 'getValue'))
            ->disableOriginalConstructor()
            ->getMock();
        $field->method('getName')->willReturn('name');
        $field->method('getValue')->willReturn('value');
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)->setMethods(array('getListeField'))->getMock();
        $config->method('getListeField')->willReturn(array($field, $field));
        /** @var $config \FMUP\Import\Config */
        $this->assertSame("0 \tname \tvalue \t\n1 \tname \tvalue \t\n", (string)$config);
    }

    public function testSortByPriority()
    {
        $method = new \ReflectionMethod(\FMUP\Import\Config::class, 'sortByPriority');
        $method->setAccessible(true);
        $config = new \FMUP\Import\Config;
        $comparingA = $this->getMockBuilder(\stdClass::class)->setMethods(array('getPriorite'))->getMock();
        $comparingA->method('getPriorite')->willReturnOnConsecutiveCalls(1, 2, 2, 3, 3);
        $comparingB = $this->getMockBuilder(\stdClass::class)->setMethods(array('getPriorite'))->getMock();
        $comparingB->method('getPriorite')->willReturnOnConsecutiveCalls(1, 3, 3, 2, 2);
        $this->assertSame(0, $method->invoke($config, $comparingA, $comparingB));
        $this->assertSame(-1, $method->invoke($config, $comparingA, $comparingB));
        $this->assertSame(1, $method->invoke($config, $comparingA, $comparingB));
    }
}
