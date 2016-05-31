<?php
namespace FMUP\Controller\Helper;

use FMUP\Logger;
use FMUP\Response\Header;

/**
 * Helper Download - helps you to download a file on a browser
 * @package FMUP\Controller\Helper
 * @author abizac
 */
trait Download
{
    /**
     * Download a file
     * @param string $filePath Server path to file to download
     * @param string|null $fileName Name + extension for the user to download
     * @param bool $forceDownload Force file to be downloaded. If false, displays file in browser
     * @throws \FMUP\Exception
     */
    public function download($filePath, $fileName = null, $forceDownload = true)
    {
        if (!$this instanceof \FMUP\Controller) {
            throw new \FMUP\Exception('Unable to use Download trait');
        }
        if (!file_exists($filePath)) {
            if ($this instanceof Logger\LoggerInterface && $this->hasLogger()) {
                $this->getLogger()->log(
                    Logger\Channel\System::NAME,
                    Logger::ERROR,
                    'Unable to find requested file',
                    array('filePath' => $filePath)
                );
            }
            throw new \FMUP\Exception\Status\NotFound('Unable to find requested file');
        }
        $fileName = $fileName ? $fileName : basename($filePath);
        $fInfo = new \finfo();
        $mimeType = $fInfo->file($filePath, FILEINFO_MIME_TYPE);
        /** @var $this $this */
        $this->downloadHeaders($mimeType, $fileName, $forceDownload)->send();
        $file = fopen($filePath, 'r');
        ini_set('max_execution_time', 0); //@todo find a better way
        while (!feof($file)) {
            echo fread($file, 4096);
            $this->obFlush();
        }
        fclose($file);
        /** @var $this \FMUP\Controller */
        $this->getResponse()->clearHeader();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function obFlush()
    {
        ob_flush();
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
        if (!$this instanceof \FMUP\Controller) {
            throw new \FMUP\Exception('Unable to use Download trait');
        }
        return $this->getResponse()
            ->addHeader(new Header\Pragma(Header\Pragma::MODE_PUBLIC))
            ->addHeader(new Header\Expires())
            ->addHeader(new Header\CacheControl(null, Header\CacheControl::CACHE_TYPE_PRIVATE))
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
