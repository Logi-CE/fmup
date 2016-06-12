<?php
/**
 * Id.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Validator;


class IntegerTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetCanEmpty()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Integer();
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Validator', $validator);
        $this->assertFalse($validator->getCanEmpty());
        $validator = new \FMUP\Import\Config\Field\Validator\Integer(true);
        $this->assertTrue($validator->getCanEmpty());
        $this->assertSame($validator, $validator->setCanEmpty());
        $this->assertFalse($validator->getCanEmpty());
        $this->assertSame($validator, $validator->setCanEmpty(true));
        $this->assertTrue($validator->getCanEmpty());
        $this->assertSame($validator, $validator->setCanEmpty(false));
        $this->assertFalse($validator->getCanEmpty());
    }

    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Integer;
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Validator', $validator);
        $validator->setCanEmpty(false);
        $this->assertFalse($validator->validate(''));
        $validator->setCanEmpty(true);
        $this->assertTrue($validator->validate(''));
        $validator->setCanEmpty(false);
        $this->assertTrue($validator->validate('1'));
        $this->assertTrue($validator->validate(0));
        $this->assertFalse($validator->validate(0.5));
        $this->assertTrue($validator->validate(3e10));
        $this->assertFalse($validator->validate('test'));
        $this->assertTrue($validator->validate(-1));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Integer();
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Validator', $validator);
        $this->assertSame('Le champ reçu n\'est pas un nombre entier', $validator->getErrorMessage());
    }
}
