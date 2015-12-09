<?php
namespace FMUP\Queue\Channel;

use FMUP\Queue\Exception as QueueException;

/**
 * Class Settings - Global channel settings
 * @package FMUP\Queue\Channel
 */
class Settings
{
    const PARAM_MAX_MESSAGE_SIZE = 'PARAM_MAX_MESSAGE_SIZE'; //(int) in bytes (default system)
    const PARAM_MAX_SEND_RETRY_TIME = 'PARAM_MAX_SEND_RETRY_TIME';//(int) max send retry time, default DEFAULT_RETRY_TIMES

    const PARAM_BLOCK_SEND = 'PARAM_BLOCK_SEND'; //(bool) if process must wait to be sure the message is sent (default false)
    const PARAM_BLOCK_RECEIVE = 'PARAM_BLOCK_RECEIVE'; //(bool) process will be blocked while no message is received (default false)
    const PARAM_SERIALIZE = 'PARAM_SERIALIZE'; //(bool) must serialize a message (default true)

    const DEFAULT_RETRY_TIMES = 3;

    const FLAG_BLOCK_SEND = 1;
    const FLAG_BLOCK_RECEIVE = 2;
    const FLAG_SERIALIZE = 4;

    private $settings = array();
    private $flags = self::FLAG_SERIALIZE;

    /**
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        $this->defineByArray($settings);
    }

    /**
     * Define multiple settings by a single array
     * @param array $settings
     * @return $this
     * @throws QueueException
     */
    public function defineByArray(array $settings)
    {
        foreach ($settings as $setting => $value) {
            $this->define($setting, $value);
        }
        return $this;
    }

    /**
     * Define a single setting by its name
     * @param string $setting
     * @param mixed $value
     * @return $this
     * @throws QueueException
     */
    public function define($setting, $value)
    {
        switch ($setting) {
            case self::PARAM_MAX_MESSAGE_SIZE:
                $this->setMaxMessageSize($value);
                break;
            case self::PARAM_BLOCK_SEND:
                $this->setBlockSend($value);
                break;
            case self::PARAM_SERIALIZE:
                $this->setSerialize($value);
                break;
            case self::PARAM_BLOCK_RECEIVE:
                $this->setBlockReceive($value);
                break;
            case self::PARAM_MAX_SEND_RETRY_TIME:
                $this->setMaxSendRetryTime($value);
                break;
            default:
                throw new QueueException('Setting is not defined');
                break;
        }
        return $this;
    }

    /**
     * Define max message size (default system)
     * @param int $size
     * @return $this
     */
    public function setMaxMessageSize($size = 0)
    {
        $this->settings[self::PARAM_MAX_MESSAGE_SIZE] = (int)$size;
        return $this;
    }

    /**
     * Get max message size (default system)
     * @return int
     */
    public function getMaxMessageSize()
    {
        return isset($this->settings[self::PARAM_MAX_MESSAGE_SIZE])
            ? (int)$this->settings[self::PARAM_MAX_MESSAGE_SIZE]
            : 0;
    }

    /**
     * Define if process must wait to be sure the message is sent (default false)
     * @param bool|false $blockSend
     * @return $this
     */
    public function setBlockSend($blockSend = false)
    {
        $this->flags |= ($blockSend) ? self::FLAG_BLOCK_SEND : ~self::FLAG_BLOCK_SEND;
        return $this;
    }

    /**
     * Get if process must wait to be sure the message is sent (default false)
     * @return bool
     */
    public function getBlockSend()
    {
        return (bool)($this->flags & self::FLAG_BLOCK_SEND);
    }

    /**
     * Define if process must serialize a message (default true)
     * @param bool|true $serialize
     * @return $this
     */
    public function setSerialize($serialize = true)
    {
        $this->flags |= ($serialize) ? self::FLAG_SERIALIZE : ~self::FLAG_SERIALIZE;
        return $this;
    }

    /**
     * Get if process must serialize a message (default true)
     * @return bool
     */
    public function getSerialize()
    {
        return (bool)($this->flags & self::FLAG_SERIALIZE);
    }

    /**
     * Define if process will be blocked while no message is received (default false)
     * @param bool|false $blockReceive
     * @return $this
     */
    public function setBlockReceive($blockReceive = false)
    {
        $this->flags |= ($blockReceive) ? self::FLAG_BLOCK_RECEIVE : ~self::FLAG_BLOCK_RECEIVE;
        return $this;
    }

    /**
     * Get if process will be blocked while no message is received (default false)
     * @return bool
     */
    public function getBlockReceive()
    {
        return (bool)($this->flags & self::FLAG_BLOCK_RECEIVE);
    }

    /**
     * Define max send retry time, default DEFAULT_RETRY_TIMES
     * @param int $maxRetry
     * @return $this
     */
    public function setMaxSendRetryTime($maxRetry = 0)
    {
        $this->settings[self::PARAM_MAX_SEND_RETRY_TIME] = (int)$maxRetry;
        return $this;
    }

    /**
     * Get max message size (default system)
     * @return int
     */
    public function getMaxSendRetryTime()
    {
        return isset($this->settings[self::PARAM_MAX_SEND_RETRY_TIME])
            ? (int)$this->settings[self::PARAM_MAX_SEND_RETRY_TIME]
            : self::DEFAULT_RETRY_TIMES;
    }
}
