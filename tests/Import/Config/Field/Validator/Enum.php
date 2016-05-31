<?php
/**
 * Boolean.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Validator;


class EnumTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetValues()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Enum();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame(array(), $validator->getValues());
        $validator = new \FMUP\Import\Config\Field\Validator\Enum(array('value1'));
        $this->assertSame(array('value1'), $validator->getValues());
        $this->assertSame($validator, $validator->setValues());
        $this->assertSame(array(), $validator->getValues());
        $this->assertSame($validator, $validator->setValues(array('value2', 'value3')));
        $this->assertSame(array('value2', 'value3'), $validator->getValues());
    }

    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Enum(array('value2', 'value3'));
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->validate('value1'));
        $this->assertTrue($validator->validate('value2'));
        $this->assertTrue($validator->validate('value3'));
        $this->assertFalse($validator->validate('value4'));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Enum();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Le champ reçu n\'est pas valide', $validator->getErrorMessage());
    }
}
