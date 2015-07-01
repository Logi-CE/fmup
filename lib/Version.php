<?php
namespace FMUP;

/**
 * Retrieve FMUP Version
 * @package FMUP
 */
class Version
{
    static private $instance;
    private $structure;

    /**
     * @return Version
     */
    static public function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Get version name
     * @return string
     */
    public function get()
    {
        return $this->getStructure()->version;
    }

    /**
     * Get composer json path
     * @return string
     */
    protected function getComposerPath()
    {
        return implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'composer.json'));
    }

    /**
     * Get composer file structure
     * @return Object
     */
    private function getStructure()
    {
        if (!$this->structure) {
            if (!is_file($this->getComposerPath())) {
                throw new \LogicException('composer.json does not exist');
            }
            $this->structure = json_decode(file_get_contents($this->getComposerPath()));
            if (!$this->structure) {
                throw new \LogicException('composer.json invalid structure');
            }
        }
        return $this->structure;
    }
}
