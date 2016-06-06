<?php
/**
 * ContentTransferEncoding.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class ContentTransferEncodingTest extends \PHPUnit_Framework_TestCase
{
    public function testContruct()
    {
        $contentTransferEncoding = new \FMUP\Response\Header\ContentTransferEncoding();
        $this->assertInstanceOf(\FMUP\Response\Header::class, $contentTransferEncoding);
        $this->assertSame(\FMUP\Response\Header\ContentTransferEncoding::TRANSFER_BINARY, $contentTransferEncoding->getValue());

        $contentTransferEncoding = new \FMUP\Response\Header\ContentTransferEncoding(
            \FMUP\Response\Header\ContentTransferEncoding::TRANSFER_BASE64
        );
        $this->assertSame(\FMUP\Response\Header\ContentTransferEncoding::TRANSFER_BASE64, $contentTransferEncoding->getValue());
    }

    public function testGetType()
    {
        $contentTransferEncoding = new \FMUP\Response\Header\ContentTransferEncoding();
        $this->assertSame(\FMUP\Response\Header\ContentTransferEncoding::TYPE, $contentTransferEncoding->getType());
    }
}
