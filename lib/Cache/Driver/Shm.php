<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;

class Shm implements CacheInterface
{
    const SETTING_NAME = 'SETTING_NAME';
    const SETTING_SIZE = 'SETTING_SIZE';
    private $shmInstance = null;

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
     * Internal method to secure a SHM name
     * @param string $name
     * @return int
     */
    private function secureName($name = null)
    {
        if (is_numeric($name)) {
            return (int)$name;
        }
        if (is_null($name)) {
            return 1;
        }
        return $this->stringToUniqueId($name);
    }

    /**
     * Convert string to a unique id
     * @param string $string
     * @return int
     */
    private function stringToUniqueId($string)
    {
        if (is_numeric($string)) {
            return (int) $string;
        }
        $length = strlen($string);
        $return = '';
        for ($i = 0; $i < $length; $i++) {
            $return .= ord($string{$i});
        }
        return (int) $return;
    }

    /**
     * Get SHM resource
     * @return resource
     */
    private function getShm()
    {
        if (!$this->shmInstance) {
            $memorySize = $this->getSetting(self::SETTING_SIZE);
            $this->shmInstance = shm_attach(
                $this->secureName($this->getSetting(self::SETTING_NAME)),
                is_numeric($memorySize) ? (int)$memorySize : null
            );
        }
        return $this->shmInstance;
    }

    /**
     * Retrieve stored value
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return ($this->has($key)) ? shm_get_var($this->getShm(), $key) : null;
    }

    /**
     * Check whether key exists in SHM
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return shm_has_var($this->getShm(), $key);
    }

    /**
     * Remove a stored key if exists
     * @param string $key
     * @return $this
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            shm_remove_var($this->getShm(), $key);
        }
        return $this;
    }

    /**
     * Define a key in SHM
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        shm_put_var($this->getShm(), $key, $value);
        return $this;
    }
}
