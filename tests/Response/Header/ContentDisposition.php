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
        $cacheControl = new \FMUP\Response\Header\ContentDisposition();
        $this->assertSame('attachment', $cacheControl->getValue());
        $cacheControl = new \FMUP\Response\Header\ContentDisposition('', 'test.png');
        $this->assertSame('filename="test.png"', $cacheControl->getValue());
        $cacheControl = new \FMUP\Response\Header\ContentDisposition('disp', 'test.png');
        $this->assertSame('disp; filename="test.png"', $cacheControl->getValue());
    }

    public function testSetGetFilename()
    {
        $cacheControl = new \FMUP\Response\Header\ContentDisposition();
        $this->assertNull($cacheControl->getFileName());
        $this->assertSame($cacheControl, $cacheControl->setFileName('billy'));
        $this->assertSame('billy', $cacheControl->getFileName());
        $this->assertSame($cacheControl, $cacheControl->setFileName());
        $this->assertNull($cacheControl->getFileName());
    }

    public function testGetType()
    {
        $cacheControl = new \FMUP\Response\Header\ContentDisposition();
        $this->assertSame(\FMUP\Response\Header\ContentDisposition::TYPE, $cacheControl->getType());
    }
}
