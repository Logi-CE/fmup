<?php
namespace FMUP\ErrorHandler;


class Base extends \FMUP\ErrorHandler
{
    public function __construct()
    {
        $this
            ->add(new \FMUP\ErrorHandler\Plugin\HttpHeader())
            ->add(new \FMUP\ErrorHandler\Plugin\Log())
            ->add(new \FMUP\ErrorHandler\Plugin\Mail())
        ;
    }
}
