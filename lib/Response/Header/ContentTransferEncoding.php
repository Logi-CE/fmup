<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class ContentTransferEncoding extends Header
{
    const TYPE = 'Content-Transfer-Encoding';

    const TRANSFER_BINARY = 'binary';
    const TRANSFER_BASE64 = 'base64';

    /**
     * @param string $transfer
     */
    public function __construct($transfer = self::TRANSFER_BINARY)
    {
        $this->setValue($transfer);
    }

    /**
     * Type for the header. Can be used to determine header to send
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }
}
