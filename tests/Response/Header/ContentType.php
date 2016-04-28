<?php
/**
 * ContentType.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class ContentTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $contentType = new \FMUP\Response\Header\ContentType();
        $this->assertInstanceOf(\FMUP\Response\Header::class, $contentType);
        $this->assertSame(\FMUP\Response\Header\ContentType::MIME_TEXT_HTML, $contentType->getMime());
        $this->assertSame(\FMUP\Response\Header\ContentType::CHARSET_UTF_8, $contentType->getCharset());
        $this->assertSame('text/html;charset=utf-8', $contentType->getValue());
    }

    public function testGetValue()
    {
        $contentType = new \FMUP\Response\Header\ContentType();
        $this->assertSame($contentType, $contentType->setMime('test'));
        $this->assertSame('test', $contentType->getMime());
        $this->assertSame($contentType, $contentType->setCharset('test'));
        $this->assertSame('test', $contentType->getCharset());
        $this->assertSame('test;charset=test', $contentType->getValue());
        $this->assertSame($contentType, $contentType->setCharset());
        $this->assertNull($contentType->getCharset());
        $this->assertSame('test', $contentType->getValue());
    }

    public function testGetType()
    {
        $contentType = new \FMUP\Response\Header\ContentType();
        $this->assertSame(\FMUP\Response\Header\ContentType::TYPE, $contentType->getType());
    }
}
