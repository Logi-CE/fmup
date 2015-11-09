<?php
/**
 * Created by PhpStorm.
 * User: jmoulin
 * Date: 09/11/2015
 * Time: 09:56
 */

namespace FMUP\Environment;


use FMUP\Environment;

trait OptionalTrait
{
    private $environment;

    /**
     * Define environment
     * @param Environment|null $environment
     * @return $this
     */
    public function setEnvironment(Environment $environment = null)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return Environment|null
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Checks whether environment is defined
     * @return bool
     */
    public function hasEnvironment()
    {
        return (bool)$this->environment;
    }
}
