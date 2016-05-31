<?php
namespace FMUP\Ftp;

class Factory
{
    const DRIVER_FTP = 'Ftp';
    const DRIVER_SFTP = 'Sftp';
    
    private static $instance;
    
    private function __construct()
    {
    }

    /**
     * Design pattern Singleton
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * @return self
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
     * @param string $driver
     * @param array $params
     * @return FtpInterface
     * @throws Exception
     */
    final public function create($driver = self::DRIVER_FTP, $params = array())
    {
        $class = $this->getClassNameForDriver($driver);
        if (!class_exists($class)) {
            throw new Exception('Unable to create ' . $class);
        }
        $instance = new $class($params);
        if (!$instance instanceof FtpInterface) {
            throw new Exception('Unable to create ' . $class);
        }
        return $instance;
    }

    /**
     * Get full class name to create
     * @param string $driver
     * @return string
     */
    protected function getClassNameForDriver($driver)
    {
        return __NAMESPACE__ . '\Driver\\' . $driver;
    }
}
