<?php
namespace FMUP;

/**
 * Class ProjectVersion
 * @package LogiCE
 */
class ProjectVersion
{
    private static $instance;
    private $structure;

    /**
     * @return $this
     */
    final public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * private construct - singleton
     */
    private function __construct()
    {
    }

    /**
     * private clone - singleton
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * Get version name
     * @return string
     */
    public function get()
    {
        $version = (string)$this->getEnv('PROJECT_VERSION');
        if ($version) {
            return $version;
        }
        try {
            if (isset($this->getStructure()->version)) {
                return $this->getStructure()->version;
            }
        } catch (\LogicException $e) {
            //Do nothing since we have a fallback
        }
        return trim($this->getFromGit()) ?: 'v0.0.0';
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function getFromGit()
    {
        $rootPath = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..'));
        exec("cd $rootPath && git describe", $gitVersion, $errorCode);
        return !$errorCode ? $gitVersion : "";
    }

    /**
     * Retrieve environment variable
     * @param string $name
     * @return array|false|string
     * @codeCoverageIgnore
     */
    protected function getEnv($name)
    {
        return getenv($name);
    }

    public function name()
    {
        return $this->getStructure()->name;
    }

    /**
     * Return composer.json path to project
     * @return string
     */
    protected function getComposerPath()
    {
        return implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..', 'composer.json'));
    }

    /**
     * Get composer file structure
     * @return Object
     */
    protected function getStructure()
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
