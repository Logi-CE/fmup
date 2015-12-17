<?php
namespace FMUP\Controller\Helper;

use FMUP\Logger\LoggerTrait;
use FMUP\Response\Header;

/**
 * Helper Download - helps you to download a file on a browser
 * @package FMUP\Controller\Helper
 * @method \FMUP\Response getResponse
 * @author jmoulin
 */
trait Download
{
    /**
     * @throws \FMUP\Exception
     */
    private function checkResponse()
    {
        $allowUse = method_exists($this, 'hasResponse') && $this->hasResponse() && method_exists($this, 'getResponse');
        if (!$allowUse) {
            throw new \FMUP\Exception('Unable to use Download trait');
        }
    }

    /**
     * Download a file
     * @param string $filePath Server path to file to download
     * @param string|null $fileName Name + extension for the user to download
     * @param bool $forceDownload Force file to be downloaded. If false, displays file in browser
     * @throws \FMUP\Exception
     */
    public function download($filePath, $fileName = null, $forceDownload = true)
    {
        if (!file_exists($filePath)) {
            if ($this instanceof LoggerTrait) {
                $this->log(\FMUP\Logger::ERROR, 'Unable to find requested file', array('filePath' => $filePath));
            }
            throw new \FMUP\Exception\Status\NotFound('Unable to find requested file');
        }
        $fileName = $fileName ? $fileName : basename($filePath);
        $fInfo = new \finfo();
        $mimeType = $fInfo->file($filePath, FILEINFO_MIME_TYPE);
        $this->downloadHeaders($mimeType, $fileName, $forceDownload)->send();
        $file = fopen($filePath, 'r');
        ini_set('max_execution_time', 0); //I don't like it @todo find a better way
        while (!feof($file)) {
            echo fread($file, 4096);
            ob_flush();
        }
        fclose($file);
        $this->getResponse()->clearHeader();
    }

    /**
     * Set header to download a file
     * @param string $mimeType Mime Type to render
     * @param string|null $fileName Name + extension for the user to download
     * @param bool $forceDownload Force file to be downloaded. If false, displays file in browser
     * @return \FMUP\Response
     * @throws \FMUP\Exception
     */
    public function downloadHeaders($mimeType, $fileName = null, $forceDownload = true)
    {
        $this->checkResponse();
        return $this->getResponse()
            ->addHeader(new Header\Pragma(Header\Pragma::MODE_PUBLIC))
            ->addHeader(new Header\Expires())
            ->addHeader((new Header\CacheControl())->setCacheType(Header\CacheControl::CACHE_TYPE_PRIVATE))
            ->addHeader(new Header\ContentType($mimeType, null))
            ->addHeader(new Header\ContentTransferEncoding())
            ->addHeader(
                new Header\ContentDisposition(
                    $forceDownload ? Header\ContentDisposition::DISPOSITION_ATTACHMENT : null,
                    $fileName
                )
            );
    }
}
