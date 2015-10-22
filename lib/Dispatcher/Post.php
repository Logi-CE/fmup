<?php

namespace FMUP\Dispatcher;

class Post extends \FMUP\Dispatcher
{
    public function __construct()
    {
        parent::__construct();
        $this->addPlugin(new Plugin\Render());
    }
}
