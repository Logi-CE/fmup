<?php
namespace FMUP\Queue\Channel\Settings;

use FMUP\Queue\Channel\Settings;

/**
 * Settings for native driver
 * @package FMUP\Queue\Channel\Settings
 */
class Native extends Settings
{
    private $settings = array();

    const PARAM_RECEIVE_FORCE_SIZE = 'PARAM_RECEIVE_FORCE_SIZE'; //(bool) force size without error if message in queue is bigger than defined message size (@see PARAM_MAX_MESSAGE_SIZE) (default false)
    const PARAM_RECEIVE_MODE_EXCEPT = 'PARAM_RECEIVE_MODE_EXCEPT'; //(bool) will receive a message different than the specified type if set to true (default false)

    public function define($setting, $value)
    {
        switch ($setting) {
            case self::PARAM_RECEIVE_FORCE_SIZE:
                $this->setReceiveForceSize($value);
                break;
            case self::PARAM_RECEIVE_MODE_EXCEPT:
                $this->setReceiveModeExcept($value);
                break;
            default:
                parent::define($setting, $value);
        }
        return $this;
    }

    /**
     * Define if process force size without error if message in queue is bigger
     * than defined message size (default false)
     *
     * @see PARAM_MAX_MESSAGE_SIZE
     * @param bool|false $receiveForceSize
     * @return $this
     */
    public function setReceiveForceSize($receiveForceSize = false)
    {
        $this->settings[self::PARAM_RECEIVE_FORCE_SIZE] = (bool)$receiveForceSize;
        return $this;
    }

    /**
     * Get if process force size without error if message in queue is bigger than defined message size (default false)
     * @see PARAM_MAX_MESSAGE_SIZE
     * @return bool
     */
    public function getReceiveForceSize()
    {
        return isset($this->settings[self::PARAM_RECEIVE_FORCE_SIZE])
            ? (bool)$this->settings[self::PARAM_RECEIVE_FORCE_SIZE]
            : false;
    }

    /**
     * Define if process will receive a message different than the specified type if set to true (default false)
     * @param bool|false $receiveModeExcept
     * @return $this
     */
    public function setReceiveModeExcept($receiveModeExcept = false)
    {
        $this->settings[self::PARAM_RECEIVE_MODE_EXCEPT] = (bool)$receiveModeExcept;
        return $this;
    }

    /**
     * Get if process will receive a message different than the specified type if set to true (default false)
     * @return bool
     */
    public function getReceiveModeExcept()
    {
        return isset($this->settings[self::PARAM_RECEIVE_MODE_EXCEPT])
            ? (bool)$this->settings[self::PARAM_RECEIVE_MODE_EXCEPT]
            : false;
    }
}
