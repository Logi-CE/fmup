<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;

class File implements CacheInterface
{
    const SETTING_PATH = 'SETTING_PATH';

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * constructor of File
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        $this->setSettings($settings);
    }

    /**
     * Can define settings of the component
     * @param array $settings
     * @return $this
     */
    public function setSettings($settings = array())
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @param string $setting
     * @param mixed $value
     * @return $this
     */
    public function setSetting($setting, $value)
    {
        $this->settings[$setting] = $value;
        return $this;
    }

    /**
     * Get a specific value of setting
     * @param string $setting
     * @return mixed
     */
    public function getSetting($setting)
    {
        return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
    }

    /**
     * Get cached file content
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return file_get_contents($this->getPathByKey($key));
    }

    /**
     * Check whether cache file exists
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return file_exists($this->getPathByKey($key));
    }

    /**
     * Invalidate cache file
     * @param string $key
     * @return $this
     */
    public function remove($key)
    {
        unlink($this->getPathByKey($key));
        return $this;
    }

    /**
     * Define cache file
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function set($key, $value)
    {
        file_put_contents($this->getPathByKey($key), $value);
        return $this;
    }

    /**
     * Retrieve path to cache file by a key
     * @param string $key
     * @return string
     */
    private function getPathByKey($key)
    {
        $path = $this->getSetting(self::SETTING_PATH) . '/';
        if (is_null($path)) {
            $path = __DIR__ . '/../../../../../data/cache/';
        }
        return $path . $key;
    }
}