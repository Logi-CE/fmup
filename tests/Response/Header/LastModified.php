<?php
/**
 * LastModified.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;

class LastModifiedTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $lastModified = new \FMUP\Response\Header\LastModified(gmdate('D, d M Y H:i:s T', filemtime(__FILE__)));
        $this->assertInstanceOf('\FMUP\Response\Header', $lastModified);
        $this->assertSame(
            gmdate('D, d M Y H:i:s T', filemtime(__FILE__)),
            $lastModified->getModifiedDate()->format('D, d M Y H:i:s T')
        );
        $this->assertSame(gmdate('D, d M Y H:i:s T', filemtime(__FILE__)), $lastModified->getValue());
    }

    public function testGetType()
    {
        $lastModified = new \FMUP\Response\Header\LastModified(gmdate('D, d M Y H:i:s T', filemtime(__FILE__)));
        $this->assertSame(\FMUP\Response\Header\LastModified::TYPE, $lastModified->getType());
    }

    public function testGetModifiedDateWhenNotSet()
    {
        $lastModified = $this->getMockBuilder('\FMUP\Response\Header\LastModified')
            ->setMethods(array('__construct', 'setModifiedDate'))
            ->getMock();
        /** @var $lastModified \FMUP\Response\Header\LastModified */
        $this->assertInstanceOf('\DateTime', $lastModified->getModifiedDate());
        $this->assertNotNull($lastModified->getModifiedDate());
    }

    public function testSetModifiedDateFailsConversion()
    {
        $lastModified = $this->getMockBuilder('\FMUP\Response\Header\LastModified')
            ->setMethods(array('__construct'))
            ->getMock();
        /** @var $lastModified \FMUP\Response\Header\LastModified */
        $this->setExpectedException('\FMUP\Exception', 'Error on date format');
        $lastModified->setModifiedDate('hello world');
    }

    public function testSetModifiedDateFailsFormat()
    {
        $lastModified = $this->getMockBuilder('\FMUP\Response\Header\LastModified')
            ->setMethods(array('__construct'))
            ->getMock();
        /** @var $lastModified \FMUP\Response\Header\LastModified */
        $this->setExpectedException('\FMUP\Exception', 'Error on date format');
        $lastModified->setModifiedDate(new \stdClass());
    }
}
