<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class ContentDisposition extends Header
{
    const TYPE = 'Content-Disposition';

    const DISPOSITION_ATTACHMENT = 'attachment';
    private $fileName;

    /**
     * @param string $type disposition
     * @param string $fileName optional filename
     */
    public function __construct($type = self::DISPOSITION_ATTACHMENT, $fileName = null)
    {
        $this->setValue($type)->setFileName($fileName);
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        $value = parent::getValue();
        $fileName = $this->getFileName();
        if (!empty($fileName)) {
            return $value . ';filename="' . $fileName . '"';
        }
        return $value;
    }

    /**
     * @param null|string $fileName
     * @return $this
     */
    public function setFileName($fileName = null)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
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
