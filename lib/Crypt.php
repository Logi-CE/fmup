<?php
namespace FMUP;

use \FMUP\Crypt\Factory;

class Crypt
{
    protected $driver;
    private $driverInterface = null;

    public function __construct($driver = Factory::DRIVER_MD5)
    {
        $this->driver = $driver;
    }

    /**
     * 
     * @return \FMUP\Crypt\CryptInterface
     */
    public function getDriver()
    {
        if (!is_null($this->driverInterface)) {
            return $this->driverInterface;
        }

        $this->driverInterface = Factory::create($this->driver);
        return $this->driverInterface;
    }

    /**
     * 
     * @param  string $password
     * @return string
     */
    public function hash($password)
    {
        return $this->getDriver()->hash($password);
    }
    
    /**
     * 
     * @param string $password
     * @return string
     */
    public function unHash($password)
    {
        return $this->getDriver()->unHash($password);
    }
    
    /**
     * 
     * @param  string $password
     * @param  string $hashed_password
     * @return boolean
     */
    public function verify($password, $hashed_password)
    {
        return (bool)($this->hash($password) == $hashed_password);
    }
}
