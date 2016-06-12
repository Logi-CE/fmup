<?php
/**
 * Id.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Validator;


class MaxLengthTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\MaxLength(10);
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Validator', $validator);

        $this->assertTrue($validator->validate('1'));
        $this->assertTrue($validator->validate('0123456789'));
        $this->assertFalse($validator->validate('012345678910'));
        $validator = new \FMUP\Import\Config\Field\Validator\MaxLength(1);
        $this->assertTrue($validator->validate(''));
        $this->assertTrue($validator->validate('t'));
        $this->assertFalse($validator->validate('test'));
        $this->assertFalse($validator->validate('é'));
        $validator = new \FMUP\Import\Config\Field\Validator\MaxLength(10);
        $this->assertTrue($validator->validate('test'));
        $validator = new \FMUP\Import\Config\Field\Validator\MaxLength(-1);
        $this->assertFalse($validator->validate(''));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\MaxLength(1);
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Validator', $validator);
        $this->assertSame('Le champ reçu est trop grand', $validator->getErrorMessage());
    }
}
