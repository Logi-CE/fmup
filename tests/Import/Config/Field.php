<?php
/**
 * Field.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Import\Config\Field\Validator;

class Mock implements \FMUP\Import\Config\Field\Validator
{
    public function getErrorMessage()
    {
    }

    public function validate($value)
    {
    }
}

namespace Tests\Import\Config;


class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $field = new \FMUP\Import\Config\Field('name', 'value', 'destinationTable', 'destinationField', true, '');
        $this->assertSame('name', $field->getName());
        $this->assertSame('value', $field->getValue());
        $this->assertSame('destinationTable', $field->getTableCible());
        $this->assertSame('destinationField', $field->getChampCible());
        $this->assertTrue($field->getRequired());
        $this->assertSame('', $field->getType());
        $this->assertSame(array(), $field->getValidators());

        $field = new \FMUP\Import\Config\Field('name', 'value', 'destinationTable', 'destinationField');
        $this->assertFalse($field->getRequired());
        $this->assertSame(array(), $field->getValidators());

        $field = new \FMUP\Import\Config\Field('name', 'value', 'destinationTable', 'destinationField', false, 'mock');
        $this->assertFalse($field->getRequired());
        $this->assertEquals(array(new \FMUP\Import\Config\Field\Validator\Mock()), $field->getValidators());
    }

    public function testSetGetValue()
    {
        $field = new \FMUP\Import\Config\Field('name', 'value', 'destinationTable', 'destinationField', true, '');
        $this->assertSame('value', $field->getValue());
        $this->assertSame($field, $field->setValue(' value  '));
        $this->assertSame('value', $field->getValue());
    }

    public function testSetGetValidator()
    {
        $field = new \FMUP\Import\Config\Field('name', 'value', 'destinationTable', 'destinationField', true, '');
        $this->assertSame(array(), $field->getValidators());
        $this->assertSame($field, $field->addValidator(new \FMUP\Import\Config\Field\Validator\Mock));
        $this->assertEquals(array(new \FMUP\Import\Config\Field\Validator\Mock()), $field->getValidators());
        $this->assertSame($field, $field->setValidator(new \FMUP\Import\Config\Field\Validator\Mock));
        $this->assertEquals(
            array(
                new \FMUP\Import\Config\Field\Validator\Mock(),
                new \FMUP\Import\Config\Field\Validator\Mock()
            ),
            $field->getValidators()
        );
        $valid = new \FMUP\Import\Config\Field\Validator\Mock;
        $this->assertSame($field, $field->setValidator($valid, 'test'));
        $this->assertEquals(
            array(
                new \FMUP\Import\Config\Field\Validator\Mock(),
                new \FMUP\Import\Config\Field\Validator\Mock(),
                'test' => $valid,
            ),
            $field->getValidators()
        );
        $valid2 = new \FMUP\Import\Config\Field\Validator\Mock;
        $this->assertSame($field, $field->setValidator($valid2, 'test'));
        $this->assertEquals(
            array(
                new \FMUP\Import\Config\Field\Validator\Mock(),
                new \FMUP\Import\Config\Field\Validator\Mock(),
                'test' => $valid2,
            ),
            $field->getValidators()
        );
        $valid3 = new \FMUP\Import\Config\Field\Validator\Mock;
        $this->assertSame($field, $field->setValidator($valid, 0));
        $this->assertSame($field, $field->setValidator($valid2, 1));
        $this->assertSame($field, $field->setValidator($valid3, 'test'));
        $this->assertEquals(
            array(
                $valid,
                $valid2,
                'test' => $valid3,
            ),
            $field->getValidators()
        );
        $this->assertSame($valid, $field->getValidator(0));
        $this->assertSame($valid2, $field->getValidator(1));
        $this->assertSame($valid3, $field->getValidator('test'));
        $this->assertNull($field->getValidator(2));
    }

    public function testAddGetFormatter()
    {
        $field = new \FMUP\Import\Config\Field('name', 'value', 'destinationTable', 'destinationField', true, '');
        /** @var \FMUP\Import\Config\Field\Formatter $formatter */
        $formatter = $this->getMockBuilder('\FMUP\Import\Config\Field\Formatter')
            ->setMethods(array('format', 'getErrorMessage', 'hasError'))
            ->getMock();
        $this->assertSame(array(), $field->getFormatters());
        $this->assertSame($field, $field->addFormatter($formatter));
        $this->assertSame(array($formatter), $field->getFormatters());
        $this->assertSame($field, $field->addFormatter($formatter));
        $this->assertSame(array($formatter, $formatter), $field->getFormatters());
        $formatter2 = clone $formatter;
        $this->assertSame($field, $field->addFormatter($formatter2));
        $this->assertSame(array($formatter, $formatter, $formatter2), $field->getFormatters());
    }

    public function testFormatField()
    {
        $formatter = $this->getMockBuilder('\FMUP\Import\Config\Field\Formatter')
            ->setMethods(array('format', 'getErrorMessage', 'hasError'))
            ->setMockClassName('Formatter')
            ->getMock();
        $formatter->expects($this->at(0))->method('format')->with($this->equalTo('value'))->willReturn('VALUE');
        $formatter->expects($this->at(1))->method('hasError')->willReturn(false);
        $formatter->expects($this->at(2))->method('format')->with($this->equalTo('VALUE'))->willReturn(false);
        $formatter->expects($this->at(3))->method('hasError')->willReturn(true);
        $formatter->expects($this->at(4))->method('getErrorMessage')->willReturn('Error');
        $formatter->expects($this->at(5))->method('format')->with($this->equalTo(''))->willReturn('VALUE');
        $formatter->expects($this->at(6))->method('hasError')->willReturn(false);
        $field = $this->getMockBuilder('\FMUP\Import\Config\Field')
            ->setMethods(array('getFormatters'))
            ->disableOriginalConstructor()
            ->getMock();
        $field->method('getFormatters')->willReturn(array($formatter, $formatter, $formatter));
        /** @var \FMUP\Import\Config\Field $field */
        $field->setValue('value');
        $this->assertSame('value', $field->getValue());
        $this->assertSame($field, $field->formatField());
        $this->assertSame('VALUE', $field->getValue());
        $this->assertSame(array('Formatter' => 'Error'), $field->getErreurs());
    }

    public function testValidateField()
    {
        $validator = $this->getMockBuilder('\FMUP\Import\Config\Field\Validator')
            ->setMethods(array('validate', 'getErrorMessage'))
            ->setMockClassName('Validator')
            ->getMock();
        $validator->expects($this->at(0))->method('validate')->with($this->equalTo('value'))->willReturn(false);
        $validator->expects($this->at(1))->method('getErrorMessage')->willReturn('Error');
        $validator->expects($this->at(2))->method('validate')->with($this->equalTo('value'))->willReturn(true);
        $validator->expects($this->at(3))->method('validate')->with($this->equalTo('value'))->willReturn(true);
        $field = $this->getMockBuilder('\FMUP\Import\Config\Field')
            ->setMethods(array('getValidators'))
            ->disableOriginalConstructor()
            ->getMock();
        $field->method('getValidators')->willReturn(array($validator, $validator, $validator));
        /** @var \FMUP\Import\Config\Field $field */
        $field->setValue('value');
        $this->assertSame('value', $field->getValue());
        $this->assertFalse($field->validateField());
        $this->assertSame(array('Validator' => 'Error'), $field->getErreurs());
        $this->assertSame('value', $field->getValue());

        $validator = $this->getMockBuilder('\FMUP\Import\Config\Field\Validator')
            ->setMethods(array('validate', 'getErrorMessage'))
            ->setMockClassName('Validator')
            ->getMock();
        $validator->expects($this->at(0))->method('validate')->with($this->equalTo('value'))->willReturn(true);
        $validator->expects($this->at(1))->method('validate')->with($this->equalTo('value'))->willReturn(true);
        $validator->expects($this->at(2))->method('validate')->with($this->equalTo('value'))->willReturn(true);
        $field2 = $this->getMockBuilder('\FMUP\Import\Config\Field')
            ->setMethods(array('getValidators'))
            ->disableOriginalConstructor()
            ->getMock();
        $field2->method('getValidators')->willReturn(array($validator, $validator, $validator));
        /** @var \FMUP\Import\Config\Field $field2 */
        $field2->setValue('value');
        $this->assertSame('value', $field2->getValue());
        $this->assertTrue($field2->validateField());
        $this->assertSame(array(), $field2->getErreurs());
        $this->assertSame('value', $field2->getValue());
    }
}
