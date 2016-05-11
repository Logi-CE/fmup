<?php
/**
 * Boolean.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Validator;


class BooleanTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Boolean();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->validate('true'));
        $this->assertFalse($validator->validate('false'));
        $this->assertFalse($validator->validate('0'));
        $this->assertFalse($validator->validate('1'));
        $this->assertTrue($validator->validate(true));
        $this->assertTrue($validator->validate(false));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Boolean();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Le champ reçu n\'est pas un boolean', $validator->getErrorMessage());
    }
}
