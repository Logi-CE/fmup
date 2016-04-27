<?php
/**
 * ContentLength.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class ContentLengthTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $contentLength = new \FMUP\Response\Header\ContentLength(10);
        $this->assertInstanceOf(\FMUP\Response\Header::class, $contentLength);
        $this->assertSame(10, $contentLength->getContentLength());
        $this->assertSame($contentLength, $contentLength->setContentLength(1000));
        $this->assertSame(1000, $contentLength->getContentLength());
        $this->assertSame('1000', $contentLength->getValue());
    }

    public function testGetType()
    {
        $contentLength = new \FMUP\Response\Header\ContentLength(10);
        $this->assertSame(\FMUP\Response\Header\ContentLength::TYPE, $contentLength->getType());
    }
}
