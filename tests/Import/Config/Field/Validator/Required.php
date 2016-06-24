<?php
/**
 * Id.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Config\Field\Validator;


class RequiredTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Required();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->validate(''));
        $this->assertFalse($validator->validate(false));
        $this->assertFalse($validator->validate(null));
        $this->assertTrue($validator->validate(10));
        $this->assertTrue($validator->validate(-10));
        $this->assertTrue($validator->validate('test'));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Required();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Ce champ est obligatoire mais aucune donnée n\'a été reçue', $validator->getErrorMessage());
    }
}
