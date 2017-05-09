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
        $version = getenv('PROJECT_VERSION');
        if ($version) {
            return $version;
        }
        if (isset($this->getStructure()->version)) {
            return $this->getStructure()->version;
        }
        $rootPath = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..'));
        exec("cd $rootPath && git describe", $gitVersion, $errorCode);
        if (!$errorCode && trim($gitVersion)) {
            return trim($gitVersion);
        }
        $stringFromFile = file(implode(DIRECTORY_SEPARATOR, array($rootPath, '.git', 'HEAD')));
        $firstLine = $stringFromFile[0]; //get the string from the array
        $explodedString = explode("/", $firstLine, 3); //seperate out by the "/" in the string
        return $explodedString[2]; //get the one that is always the branch name
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
