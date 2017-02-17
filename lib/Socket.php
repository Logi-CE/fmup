<?php
/**
 * @author jyamin
 */

namespace FMUP;

class Socket
{
    /** @var resource $socket */
    protected $socket;

    /** @var int $errorNumber */
    protected $errorNumber;

    /** @var string $errorString */
    protected $errorString;

    /**
     * @param $host
     * @param $port
     * @return $this
     */
    public function connect($host, $port)
    {
        $this->socket = $this->phpFSockOpen($host, $port, $this->errorNumber, $this->errorString);
        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return (bool)$this->socket;
    }

    /**
     * @param $str
     * @param int|null $length
     * @return int
     */
    public function write($str, $length = null)
    {
        if ($length) {
            return $this->phpFWrite($this->socket, $str, $length);
        }
        return $this->phpFWrite($this->socket, $str);
    }

    /**
     * @param $length
     * @return string
     */
    public function read($length)
    {
        return $this->phpFRead($this->socket, $length);
    }

    /**
     * @return int
     */
    public function getErrorNumber()
    {
        return $this->errorNumber;
    }

    /**
     * @return string
     */
    public function getErrorString()
    {
        return $this->errorString;
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $errno
     * @param string $errstr
     * @return resource
     * @codeCoverageIgnore
     */
    protected function phpFSockOpen($host, $port, $errno, $errstr)
    {
        return fsockopen($host, $port, $errno, $errstr);
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    protected function phpFWrite()
    {
        return call_user_func_array('fwrite', func_get_args());
    }

    /**
     * @param resource $handle
     * @param int $length
     * @return string
     * @codeCoverageIgnore
     */
    protected function phpFRead($handle, $length)
    {
        return fread($handle, $length);
    }

    /**
     * @param resource $handle
     * @return bool
     * @codeCoverageIgnore
     */
    protected function phpFClose($handle)
    {
        return fclose($handle);
    }
}
