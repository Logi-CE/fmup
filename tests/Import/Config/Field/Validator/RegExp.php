<?php
/**
 * Id.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Config\Field\Validator;


class RegExpTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGetCanEmpty()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\RegExp();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->getAllowEmpty());
        $validator = new \FMUP\Import\Config\Field\Validator\RegExp(null, true);
        $this->assertTrue($validator->getAllowEmpty());
        $this->assertSame($validator, $validator->setAllowEmpty());
        $this->assertFalse($validator->getAllowEmpty());
        $this->assertSame($validator, $validator->setAllowEmpty(true));
        $this->assertTrue($validator->getAllowEmpty());
        $this->assertSame($validator, $validator->setAllowEmpty(false));
        $this->assertFalse($validator->getAllowEmpty());
    }

    public function testSetGetExpression()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\RegExp();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('', $validator->getExpression());
        $validator = new \FMUP\Import\Config\Field\Validator\RegExp('test');
        $this->assertSame('test', $validator->getExpression());
        $this->assertSame($validator, $validator->setExpression('~testReg~'));
        $this->assertSame('~testReg~', $validator->getExpression());
    }

    public function testValidate()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\RegExp('~^[0-9]+$~');
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertFalse($validator->validate(''));
        $this->assertSame($validator, $validator->setAllowEmpty(true));
        $this->assertTrue($validator->validate(''));
        $this->assertTrue($validator->validate(0));
        $this->assertFalse($validator->validate(0.5));
        $this->assertTrue($validator->validate(3e10));
        $this->assertFalse($validator->validate('test'));
        $this->assertFalse($validator->validate(-1));
        $this->assertSame($validator, $validator->setExpression('~^\-?[0-9]+$~'));
        $this->assertTrue($validator->validate(''));
        $this->assertTrue($validator->validate(0));
        $this->assertFalse($validator->validate(0.5));
        $this->assertTrue($validator->validate(3e10));
        $this->assertFalse($validator->validate('test'));
        $this->assertTrue($validator->validate(-1));
    }

    public function testGetErrorMessage()
    {
        $validator = new \FMUP\Import\Config\Field\Validator\RegExp();
        $this->assertInstanceOf(\FMUP\Import\Config\Field\Validator::class, $validator);
        $this->assertSame('Le champ reçu ne correspond pas au format autorisé', $validator->getErrorMessage());
    }
}
