<?php
namespace FMUP\Routing\Route;

use FMUP\Routing\Route;

class AssetCompressor extends Route
{
    const CSS_EXTENSION = 'css';
    const JS_EXTENSION = 'js';
    private $extension;
    private $assets;
    private $version;

    /**
     * Must return true if URI can be handled by route
     * @return bool
     */
    public function canHandle()
    {
        $request = $this->getRequest()->getRequestUri();
        if (preg_match("~^/(styles|scripts)/cached/([^/]+)/(.+).(css|js)$~", $request, $matches)) {
            if (count($matches) == 5) {
                if (
                    ($matches[1] == 'styles' && $matches[4] == 'css') ||
                    ($matches[1] == 'scripts' && $matches[4] == 'js')
                ) {
                    $this->version = $matches[2];
                    $this->assets = $matches[3];
                    $this->extension = $matches[4];
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @throws \FMUP\Exception
     */
    public function handle()
    {
        $this->getRequest()->setGetValue('version', $this->version)->setGetValue('asset', $this->assets);
    }

    /**
     * Must return action to call
     * @return string
     */
    public function getAction()
    {
        return $this->extension;
    }

    /**
     * Must return Controller class name
     * @return string
     */
    public function getControllerName()
    {
        return '\FMUP\Controller\AssetCompressor';
    }
}
