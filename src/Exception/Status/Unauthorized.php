<?php
namespace FMUP\Exception\Status;

/**
 * Class Unauthorized - Exception to explain to framework that this page cannot be handled due to unconnected used
 * @package FMUP\Exception
 */
class Unauthorized extends \FMUP\Exception\Status
{
    /**
     * Must return understandable status
     * @see FMUP\Response\Header
     * @return string
     */
    public function getStatus()
    {
        return \FMUP\Response\Header\Status::VALUE_UNAUTHORIZED;
    }
}
