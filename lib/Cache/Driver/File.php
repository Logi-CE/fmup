<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;
use FMUP\Cache\Exception;

class File implements CacheInterface
{
    const SETTING_PATH = 'SETTING_PATH';
    const SETTING_SERIALIZE = 'SETTING_SERIALIZE';

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
     * @throws Exception
     */
    public function get($key)
    {
        return $this->has($key) ? $this->unSerialize(file_get_contents($this->getPathByKey($key))) : null;
    }

    /**
     * Check whether cache file exists
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->fileExists($this->getPathByKey($key));
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
     * @throws Exception if unable to create cache folder
     */
    public function set($key, $value)
    {
        $dirName = dirname($this->getPathByKey($key));
        if (!$this->fileExists($dirName)) {
            if (!$this->mkDir($dirName)) {
                throw new Exception('Error while trying to create cache folder ' . $dirName);
            }
        }
        file_put_contents($this->getPathByKey($key), $this->serialize($value));
        return $this;
    }

    /**
     * @param $dirName
     * @return bool
     * @codeCoverageIgnore
     */
    protected function fileExists($dirName)
    {
        return file_exists($dirName);
    }

    /**
     * @param $dirName
     * @return bool
     * @codeCoverageIgnore
     */
    protected function mkDir($dirName)
    {
        return mkdir($dirName, 0755, true);
    }

    /**
     * Retrieve path to cache file by a key
     * @param string $key
     * @return string
     */
    private function getPathByKey($key)
    {
        $path = $this->getSetting(self::SETTING_PATH);
        if (is_null($path)) {
            $path = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..', '..', 'data', 'cache'));
        }
        return $path . DIRECTORY_SEPARATOR . $key;
    }

    /**
     * Serialize a content
     * @param mixed $content
     * @return string
     */
    private function serialize($content)
    {
        if ((bool)$this->getSetting(self::SETTING_SERIALIZE)) {
            return serialize($content);
        }
        return $content;
    }

    /**
     * UnSerialize a content
     * @param string $content
     * @return mixed|string
     */
    private function unSerialize($content)
    {
        if ((bool)$this->getSetting(self::SETTING_SERIALIZE)) {
            return unserialize($content);
        }
        return $content;
    }
}
