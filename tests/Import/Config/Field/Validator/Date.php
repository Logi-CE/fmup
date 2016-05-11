<?php
/**
 * Boolean.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Config\Field\Validator;


class DateTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetCanEmpty()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Date();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->canEmpty());
        $validator = new \FMUP\Import\Config\Field\Validator\Date(true);
        $this->assertTrue($validator->canEmpty());
        $this->assertSame($validator, $validator->setCanEmpty());
        $this->assertFalse($validator->canEmpty());
        $this->assertSame($validator, $validator->setCanEmpty(true));
        $this->assertTrue($validator->canEmpty());
        $this->assertSame($validator, $validator->setCanEmpty(false));
        $this->assertFalse($validator->canEmpty());
    }

    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Date;
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $validator->setCanEmpty(false);
        $this->assertFalse($validator->validate(''));
        $validator->setCanEmpty(true);
        $this->assertTrue($validator->validate(''));
        $validator->setCanEmpty(false);
        $this->assertTrue($validator->validate('10/10/2010'));
        $this->assertFalse($validator->validate('10/10/2010 10:10:10'));
        $this->assertFalse($validator->validate('bidibidibidi'));
        $this->assertTrue($validator->validate('2010-10-10'));
        $this->assertFalse($validator->validate('2010-10-10 10:10:10'));
        $this->assertTrue($validator->validate('10-10-10'));
        $this->assertFalse($validator->validate('10-10-10 10:10:10'));
        $this->assertTrue($validator->validate('101010'));
        $this->assertFalse($validator->validate('90102010'));
        $this->assertTrue($validator->validate('20101010'));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\Date();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Le champ reçu n\'est pas une date valide', $validator->getErrorMessage());
    }
}
