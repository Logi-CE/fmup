<?php
namespace FMUP\Dispatcher\Plugin;

/**
 * Class Version - Add project version to all CSS + JS to avoid/allow browser cache
 * @package FMUP\Dispatcher\Plugin
 */
class Version extends \FMUP\Dispatcher\Plugin
{
    protected $name = 'Version';

    public function handle()
    {
        $this->getResponse()->setBody(
            preg_replace(
                '/(<(?:script|link)[^>]+(?:src|href)=["\'][^"\'?]+)(\?[^"\']+)?([\'"])/s',
                '$1?' . \FMUP\ProjectVersion::getInstance()->get() . '$3',
                $this->getResponse()->getBody()
            )
        );
        return $this;
    }
}
