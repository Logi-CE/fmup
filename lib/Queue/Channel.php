<?php
namespace FMUP\Queue;

use FMUP\Queue\Exception;

class Channel
{
    private $name;
    private $settings;
    private $resource;

    /**
     * @param string $name
     * @param Channel\Settings|null $settings
     */
    public function __construct($name, Channel\Settings $settings = null)
    {
        $this->setName($name)->setSettings($settings);
    }

    /**
     * Define name
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string)$name;
        return $this;
    }

    /**
     * Retrieve name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Define settings
     * @param Channel\Settings|null $settings
     * @return $this
     */
    public function setSettings(Channel\Settings $settings = null)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Retrieve settings
     * @return Channel\Settings
     */
    public function getSettings()
    {
        if (!$this->settings) {
            $this->settings = new Channel\Settings();
        }
        return $this->settings;
    }

    /**
     * Get channel resource
     * @return mixed
     * @throws \FMUP\Queue\Exception
     */
    public function getResource()
    {
        if (!$this->resource) {
            throw new Exception('Resource must be set before using it');
        }
        return $this->resource;
    }

    /**
     * Define channel resource
     * @param mixed $resource
     * @return $this
     */
    public function setResource($resource = null)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Check whether resource is computed
     * @return bool
     */
    public function hasResource()
    {
        return (bool)$this->resource;
    }
}
