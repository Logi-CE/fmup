<?php
/**
 * ContentDisposition.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class ContentDispositionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $contentDisposition = new \FMUP\Response\Header\ContentDisposition();
        $this->assertInstanceOf(\FMUP\Response\Header::class, $contentDisposition);
        $this->assertSame('attachment', $contentDisposition->getValue());
        $contentDisposition = new \FMUP\Response\Header\ContentDisposition('', 'test.png');
        $this->assertSame('filename="test.png"', $contentDisposition->getValue());
        $contentDisposition = new \FMUP\Response\Header\ContentDisposition('disp', 'test.png');
        $this->assertSame('disp; filename="test.png"', $contentDisposition->getValue());
    }

    public function testSetGetFilename()
    {
        $contentDisposition = new \FMUP\Response\Header\ContentDisposition();
        $this->assertNull($contentDisposition->getFileName());
        $this->assertSame($contentDisposition, $contentDisposition->setFileName('billy'));
        $this->assertSame('billy', $contentDisposition->getFileName());
        $this->assertSame($contentDisposition, $contentDisposition->setFileName());
        $this->assertNull($contentDisposition->getFileName());
    }

    public function testGetType()
    {
        $contentDisposition = new \FMUP\Response\Header\ContentDisposition();
        $this->assertSame(\FMUP\Response\Header\ContentDisposition::TYPE, $contentDisposition->getType());
    }
}
