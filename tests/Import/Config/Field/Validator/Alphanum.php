<?php
/**
 * Alphanum.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Validator;


class AlphanumTest extends \PHPUnit_Framework_TestCase
{
    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Alphanum();
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Validator', $validator);
        $this->assertTrue($validator->validate('test'));
        $this->assertTrue($validator->validate('test091'));
        $this->assertFalse($validator->validate('test.091'));
        $this->assertTrue($validator->validate('TEST'));
        $this->assertTrue($validator->validate('TEST091'));
        $this->assertFalse($validator->validate('TEST.091'));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Alphanum();
        $this->assertInstanceOf('\FMUP\Import\Config\Field\Validator', $validator);
        $this->assertSame('Le champ reçu n\'est pas alphanumérique', $validator->getErrorMessage());
    }
}
