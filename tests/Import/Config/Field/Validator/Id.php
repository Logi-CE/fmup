<?php
/**
 * Id.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Config\Field\Validator;


class IdTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Id;
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertTrue($validator->validate('1'));
        $this->assertTrue($validator->validate(0));
        $this->assertFalse($validator->validate(0.5));
        $this->assertTrue($validator->validate(3e10));
        $this->assertFalse($validator->validate('test'));
        $this->assertFalse($validator->validate(-1));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Id();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Le champ reçu n\'est pas un id', $validator->getErrorMessage());
    }
}
