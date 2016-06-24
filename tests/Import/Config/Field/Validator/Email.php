<?php
/**
 * Boolean.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Config\Field\Validator;


class EmailTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetCanEmpty()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Email();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->getCanEmpty());
        $validator = new \FMUP\Import\Config\Field\Validator\Email(true);
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
        $validator = new \FMUP\Import\Config\Field\Validator\Email;
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $validator->setCanEmpty(false);
        $this->assertFalse($validator->validate(''));
        $validator->setCanEmpty(true);
        $this->assertTrue($validator->validate(''));
        $validator->setCanEmpty(false);
        $this->assertFalse($validator->validate('10/10/2010'));
        $this->assertFalse($validator->validate('bidibidibidi'));
        $this->assertTrue($validator->validate('t@t.co'));
        $this->assertTrue($validator->validate('test@test.co'));
        $this->assertTrue($validator->validate('test@test.press'));
        $this->assertFalse($validator->validate('test@test..press'));
        $this->assertTrue($validator->validate('test@test.test.press'));
        $this->assertFalse($validator->validate('test@test.family'));
        $this->assertTrue($validator->validate('200@300.press'));
        $this->assertFalse($validator->validate('test@test.200'));
        $this->assertTrue($validator->validate('TEST@TEST.PRESS'));
        $this->assertTrue($validator->validate('200.@test.com'));
        $this->assertTrue($validator->validate('200.300@test.com'));
        $this->assertTrue($validator->validate('200.300.t@test.com'));
        $this->assertTrue($validator->validate('200.300.t.888@test.com'));
        $this->assertTrue($validator->validate('200.300@test.test.com'));
        $this->assertTrue($validator->validate('200.300@press.service.co.uk'));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Email();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Le champ reçu n\'est pas un email valide', $validator->getErrorMessage());
    }
}
