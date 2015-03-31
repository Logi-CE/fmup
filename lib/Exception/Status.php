<?php
namespace FMUP\Exception;

/**
 * Class NotFound - Exception to explain to framework that this page cannot be handled
 * @package FMUP\Exception
 */
abstract class Status extends \FMUP\Exception
{
    /**
     * Must return understandable status
     * @see FMUP\Response\Header
     * @return string
     */
    abstract function getStatus();
}
