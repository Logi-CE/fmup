<?php
namespace FMUP;

use FMUP\Ftp\Factory;

class Ftp
{
    const DRIVER = 'driver';
    protected $driver;
    protected $params = array();
    private $driverInstance = null;

    /**
     * @var Factory
     */
    private $factory;

    public function __construct($params = array())
    {
        $this->driver = isset($params[self::DRIVER]) ? $params[self::DRIVER] : Factory::DRIVER_FTP; 
        $this->params = $params;
    }

    /**
     * @return Ftp\FtpInterface
     * @throws Ftp\Exception
     */
    public function getDriver()
    {
        if (!is_null($this->driverInstance)) {
            return $this->driverInstance;
        }

        $driverInstance = $this->getFactory()->create($this->driver, $this->params);
        $this->driverInstance = $driverInstance;
        return $this->driverInstance;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        if (!$this->factory) {
            $this->factory = Factory::getInstance();
        }
        return $this->factory;
    }

    /**
     * @param Factory $factory
     * @return $this
     */
    public function setFactory(Factory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @param string $host
     * @param int $port
     * @return $this
     */
    public function connect($host, $port = 21)
    {
        $this->getDriver()->connect($host, $port);
        return $this;
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool
     */
    public function login($user, $pass)
    {
        return $this->getDriver()->login($user, $pass);
    }

    /**
     * @param string $localFile
     * @param string $remoteFile
     * @return bool
     */
    public function get($localFile, $remoteFile)
    {
        return $this->getDriver()->get($localFile, $remoteFile);
    }

    /**
     * @param $file
     * @return bool
     */
    public function delete($file)
    {
        return $this->getDriver()->delete($file);
    }

    /**
     * @return bool
     */
    public function close()
    {
        return $this->getDriver()->close();
    }
}
