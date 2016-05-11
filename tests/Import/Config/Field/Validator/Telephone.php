<?php
/**
 * Id.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Validator;


class TelephoneTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetCanEmpty()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Telephone();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->getCanEmpty());
        $validator = new \FMUP\Import\Config\Field\Validator\Telephone(true);
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
        $validator = new \FMUP\Import\Config\Field\Validator\Telephone();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $validator->setCanEmpty(false);
        $this->assertFalse($validator->validate(''));
        $validator->setCanEmpty(true);
        $this->assertTrue($validator->validate(''));
        $validator->setCanEmpty(false);
        $this->assertFalse($validator->validate(1));
        $this->assertFalse($validator->validate('test'));
        $this->assertTrue($validator->validate('          '));
        $this->assertTrue($validator->validate('                    '));
        $this->assertTrue($validator->validate('+                    '));
        $this->assertFalse($validator->validate('+                     '));
        $this->assertTrue($validator->validate("+\t\t\t\t\t\t\t\t\t\t"));
        $this->assertTrue($validator->validate("+\t\n\t\n\t\n\t\n\t\t"));
        $this->assertTrue($validator->validate("+\t\r\t\n\v\n\t\f\r\t"));
        $this->assertTrue($validator->validate('+01234567890213'));
        $this->assertTrue($validator->validate('+01.23(45 67)89'));
        $this->assertFalse($validator->validate('01-23-45-67-89'));
        $this->assertTrue($validator->validate('01)23)45)67)89'));
        $this->assertTrue($validator->validate('9999999999'));
        $this->assertFalse($validator->validate('18'));
        $this->assertFalse($validator->validate('118218'));
        $this->assertTrue($validator->validate('+(33)1.23.45.67.89'));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Telephone();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Le champ reçu n\'est pas un téléphone valide', $validator->getErrorMessage());
    }
}
