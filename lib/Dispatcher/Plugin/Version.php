<?php
namespace FMUP\Dispatcher\Plugin;

/**
 * Class Version - Add project version to all CSS + JS to avoid/allow browser cache
 * @package FMUP\Dispatcher\Plugin
 */
class Version extends \FMUP\Dispatcher\Plugin
{
    protected $name = 'Version';
    protected $version;

    public function handle()
    {
        $this->getResponse()->setBody(
            preg_replace(
                '/(<(?:script|link)[^>]+(?:src|href)=["\'][^"\'?]+)(\?[^"\']+)?([\'"])/s',
                '$1?' . $this->getVersion() . '$3',
                $this->getResponse()->getBody()
            )
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        if (!$this->version) {
            $this->version = $this->getFromProjectVersion();
        }
        return $this->version;
    }

    /**
     * @param $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = (string) $version;
        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function getFromProjectVersion()
    {
        return \FMUP\ProjectVersion::getInstance()->get();
    }
}
