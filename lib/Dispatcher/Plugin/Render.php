<?php
namespace FMUP\Dispatcher\Plugin;

class Render extends \FMUP\Dispatcher\Plugin
{
    protected $name = 'Render';

    /**
     * Can be used to apply something on request object
     */
    public function handle()
    {
        $this->getResponse()->send();
        return $this;
    }
}
