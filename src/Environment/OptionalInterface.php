<?php
namespace FMUP\Environment;

use FMUP\Environment;

interface OptionalInterface
{
    /**
     * Define environment
     * @param Environment|null $environment
     * @return $this
     */
    public function setEnvironment(Environment $environment = null);

    /**
     * @return Environment
     */
    public function getEnvironment();

    /**
     * Checks whether environment is defined
     * @return bool
     */
    public function hasEnvironment();
}
