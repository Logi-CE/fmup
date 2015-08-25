<?php
namespace FMUP\Controller;

use FMUP\Cache;
use FMUP\Controller;
use FMUP\Exception\Status\NotFound;
use FMUP\Response\Header\CacheControl;
use FMUP\Response\Header\ContentType;
use FMUP\Cache\Driver\File as CacheFile;
use JShrink\Minifier as JSMinifier;

class AssetCompressor extends Controller
{
    const PATH_SEPARATOR = ',';
    const CACHE_TIME = '+10 years';
    const CSS_EXTENSION = '.css';
    const JS_EXTENSION = '.js';
    const REPLACE_SEPARATOR = '~';

    private $cacheFile;
    private $cssMinifier;
    private $jsMinifier;

    /**
     * @return CacheFile
     */
    public function getCacheFile()
    {
        if (!$this->cacheFile) {
            $this->cacheFile = new CacheFile(
                array(
                    CacheFile::SETTING_PATH => implode(
                        DIRECTORY_SEPARATOR,
                        array(BASE_PATH, 'public', APPLICATION)
                    ),
                )
            );
        }
        return $this->cacheFile;
    }

    public function setCache(CacheFile $cache)
    {
        $this->cacheFile = $cache;
        return $this;
    }

    public function cssAction()
    {
        $assetString = $this->getRequest()->get('asset');
        $version = $this->getRequest()->get('version');

        $cacheKey = implode(DIRECTORY_SEPARATOR, array('styles', 'cached', $version, $assetString . self::CSS_EXTENSION));

        if (!$this->getCacheFile()->has($cacheKey)) {
            $assets = explode(self::PATH_SEPARATOR, $assetString);
            $content = '';
            foreach ($assets as $asset) {
                $assetPath = implode(
                    DIRECTORY_SEPARATOR,
                    array(
                        BASE_PATH,
                        'public',
                        APPLICATION,
                        str_replace(self::REPLACE_SEPARATOR, DIRECTORY_SEPARATOR, $asset) . self::CSS_EXTENSION
                    )
                );
                if (!file_exists($assetPath)) {
                    throw new NotFound('Resource ' . $assetPath . ' not found');
                }
                $content .= file_get_contents($assetPath);
            }
            $compressedContent = $this->getCssMinifier()->minify($content);
            $this->getCacheFile()->set($cacheKey, $compressedContent);
        } else {
            $compressedContent = $this->getCacheFile()->get($cacheKey);
        }

        $this->getResponse()
            ->addHeader(new CacheControl(new \DateTime(self::CACHE_TIME)))
            ->addHeader(new ContentType(ContentType::MIME_TEXT_CSS))
            ->setBody($compressedContent);
    }

    public function jsAction()
    {
        $assetString = $this->getRequest()->get('asset');
        $version = $this->getRequest()->get('version');

        $cacheKey = implode(DIRECTORY_SEPARATOR, array('scripts', 'cached', $version, $assetString . self::JS_EXTENSION));

        if (!$this->getCacheFile()->has($cacheKey)) {
            $assets = explode(self::PATH_SEPARATOR, $assetString);
            $content = '';
            foreach ($assets as $asset) {
                $assetPath = implode(
                    DIRECTORY_SEPARATOR,
                    array(
                        BASE_PATH,
                        'public',
                        APPLICATION,
                        str_replace(self::REPLACE_SEPARATOR, DIRECTORY_SEPARATOR, $asset) . self::JS_EXTENSION
                    )
                );
                if (!file_exists($assetPath)) {
                    throw new NotFound('Resource ' . $assetPath . ' not found');
                }
                $content .= file_get_contents($assetPath) . "\n";
            }
            $compressedContent = $this->getJsMinifier()->minify($content);
            $this->getCacheFile()->set($cacheKey, $compressedContent);
        } else {
            $compressedContent = $this->getCacheFile()->get($cacheKey);
        }

        $this->getResponse()
            ->addHeader(new CacheControl(new \DateTime(self::CACHE_TIME)))
            ->addHeader(new ContentType(ContentType::MIME_APPLICATION_JS))
            ->setBody($compressedContent);
    }

    /**
     * @return \CssMinifier
     */
    public function getCssMinifier()
    {
        if (!$this->cssMinifier) {
            $this->cssMinifier = new \CssMinifier();
        }
        return $this->cssMinifier;
    }

    /**
     * @return \JShrink\Minifier
     */
    public function getJsMinifier()
    {
        if (!$this->jsMinifier) {
            $this->jsMinifier = new JSMinifier();
        }
        return $this->jsMinifier;
    }
}
