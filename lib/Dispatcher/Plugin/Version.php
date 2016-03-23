<?php
namespace FMUP\Dispatcher\Plugin;

class Version extends \FMUP\Dispatcher\Plugin
{
    /**
     * Can be used to apply something on request object
     */
    public function handle()
    {
        $this->getResponse()->setBody(
            preg_replace(
                '/(<(?:script|link)[^>]+(?:src|href)=["\'][^"\']+)([\'"])/s',
                '$1?' . \FMUP\ProjectVersion::getInstance()->get() . '$2',
                $this->getResponse()->getBody()
            )
        );
        return $this;
    }
}
