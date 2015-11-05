<?php
namespace FMUP\Queue\Driver;

use \FMUP\Queue\DriverInterface;
use \FMUP\Queue\Exception;

class Native implements DriverInterface
{
    const PARAM_MAX_MESSAGE_SIZE = 'PARAM_MAX_MESSAGE_SIZE'; //(int) in bytes (default system)
    const PARAM_BLOCK_SEND = 'PARAM_BLOCK_SEND'; //(bool) if process must wait to be sure the message is sent (default false)
    const PARAM_SERIALIZE = 'PARAM_SERIALIZE'; //(bool) must serialize a message (default true)
    const PARAM_RECEIVE_FORCE_SIZE = 'PARAM_RECEIVE_FORCE_SIZE'; //(bool) force size without error if message in queue is bigger than defined message size (@see PARAM_MAX_MESSAGE_SIZE) (default false)
    const PARAM_BLOCK_RECEIVE = 'PARAM_BLOCK_RECEIVE'; //(bool) process will be blocked while no message is received (default false)
    const PARAM_RECEIVE_MODE_EXCEPT = 'PARAM_RECEIVE_MODE_EXCEPT'; //(bool) will receive a message different than the specified type if set to true (default false)

    const CONFIGURATION_PERM_UID = 'msg_perm.uid';
    const CONFIGURATION_PERM_GID = 'msg_perm.gid';
    const CONFIGURATION_PERM_MODE = 'msg_perm.mode';
    const CONFIGURATION_SENT_TIME = 'msg_stime';
    const CONFIGURATION_RECEIVED_TIME = 'msg_rtime';
    const CONFIGURATION_UPDATE_TIME = 'msg_ctime';
    const CONFIGURATION_MESSAGE_NUMBER = 'msg_qnum';
    const CONFIGURATION_MESSAGE_SIZE = 'msg_qbytes';
    const CONFIGURATION_SENDER_PID = 'msg_lspid';
    const CONFIGURATION_RECEIVER_PID = 'msg_lrpid';

    private $settings = array();

    /**
     * Connect to specified queue
     * @param string|int $name
     * @return resource
     * @throws Exception
     */
    public function connect($name)
    {
        $name = $this->secureQueueName($name);
        return msg_get_queue($name);
    }

    /**
     * Check if queue exists
     * @param string|int $name
     * @return bool
     * @throws Exception
     */
    public function exists($name)
    {
        $name = $this->secureQueueName($name);
        return msg_queue_exists($name);
    }

    /**
     * Get a message from queue
     *
     * @param resource $queueResource
     * @param string $messageType
     * @return mixed|null
     * @throws Exception
     */
    public function pull($queueResource, $messageType = null)
    {
        $messageType = $this->secureMessageType($messageType);
        $receivedMessageType = 0;
        $message = null;
        $error = 0;
        $messageSize = $this->getMessageSize($queueResource);
        $success = msg_receive(
            $queueResource,
            $messageType,
            $receivedMessageType,
            $messageSize,
            $message,
            $this->isSerialize(),
            $this->getReceiveFlags(),
            $error
        );
        $isNonBlockReceive = (MSG_IPC_NOWAIT & $this->getParamBlockReceive());
        $isNonBlockingPlusNoMessage = $isNonBlockReceive && ($error === MSG_ENOMSG);
        if (!$success && !$isNonBlockingPlusNoMessage) {
            throw new Exception("Error while receiving message", $error);
        }
        return $message;
    }

    /**
     * Retrieve message maximum size for a given queue
     * @param resource $queueResource
     * @return int
     */
    private function getMessageSize($queueResource)
    {
        $messageSize = $this->getSetting(self::PARAM_MAX_MESSAGE_SIZE);
        if (!$messageSize) {
            $configuration = $this->getConfiguration($queueResource);
            $messageSize = (int)$configuration[self::CONFIGURATION_MESSAGE_SIZE];
            $this->setSetting(self::PARAM_MAX_MESSAGE_SIZE, $messageSize);
        }
        return $messageSize;
    }

    /**
     * Push a message in queue
     * @param resource $queueResource
     * @param mixed $message
     * @param string|int $messageType
     * @return $this
     * @throws Exception
     */
    public function push($queueResource, $message, $messageType = null)
    {
        $messageType = $this->secureMessageType($messageType);
        $error = 0;
        $blockSend = (bool)$this->getSetting(self::PARAM_BLOCK_SEND);
        $success = msg_send($queueResource, $messageType, $message, $this->isSerialize(), $blockSend, $error);
        if (!$success) {
            throw new Exception("Error while sending message", $error);
        }
        return $this;
    }

    /**
     * Secure queue name to be int (due to semaphore)
     * @param string|int $name
     * @return int
     * @throws Exception
     */
    private function secureQueueName($name)
    {
        $name = (int)$this->stringToUniqueId($name);
        if ($name === 0) {
            throw new Exception('Queue name muse be in INT > 0 to use semaphores');
        }
        return $name;
    }

    /**
     * Secure message type
     * @param string|int $messageType
     * @return int|null
     * @throws Exception
     */
    private function secureMessageType($messageType = null)
    {
        if (is_null($messageType)) {
            $messageType = 1;
        }
        $messageType = (int)$this->stringToUniqueId($messageType);
        if ($messageType === 0) {
            throw new Exception('Queue name muse be in INT > 0 to use semaphores');
        }
        return $messageType;
    }

    /**
     * Convert string to a unique id
     * @param string $string
     * @return int
     */
    private function stringToUniqueId($string)
    {
        if (is_numeric($string)) {
            return (int) $string;
        }
        $length = strlen($string);
        $return = 0;
        for ($i = 0; $i < $length; $i++) {
            $return += ord($string{$i});
        }
        return (int) $length . '0' . $return;
    }

    /**
     * Define a setting
     * @param $paramName
     * @param null $value
     * @return $this
     */
    public function setSetting($paramName, $value = null)
    {
        $this->settings[$paramName] = $value;
        return $this;
    }

    /**
     * Get Setting Name value
     * @param string $paramName
     * @return mixed
     */
    public function getSetting($paramName)
    {
        return isset($this->settings[$paramName]) ? $this->settings[$paramName] : null;
    }

    /**
     * Get queue configuration
     * @param resource $queueResource
     * @return array
     */
    public function getConfiguration($queueResource)
    {
        return msg_stat_queue($queueResource);
    }

    /**
     * Define queue configuration
     * @param resource $queueResource
     * @param array $params
     * @return bool
     */
    public function setConfiguration($queueResource, $params)
    {
        if (isset($params[self::CONFIGURATION_MESSAGE_SIZE])) {
            $this->setSetting(self::PARAM_MAX_MESSAGE_SIZE, (int)$params[self::CONFIGURATION_MESSAGE_SIZE]);
        }
        return msg_set_queue($queueResource, (array)$params);
    }

    /**
     * Destroy a queue
     * @param resource $queueResource
     * @return bool
     */
    public function destroy($queueResource)
    {
        return msg_remove_queue($queueResource);
    }

    /**
     * Check whether serialize setting is defined or not
     * @return bool
     */
    private function isSerialize()
    {
        return is_null($this->getSetting(self::PARAM_SERIALIZE))
            ? true
            : (bool)$this->getSetting(self::PARAM_SERIALIZE);
    }

    /**
     * Reception options
     * @return int
     */
    private function getReceiveFlags()
    {
        $blockReceive = $this->getParamBlockReceive();
        $modeExcept = $this->getSetting(self::PARAM_RECEIVE_MODE_EXCEPT) ? MSG_EXCEPT : 0;
        $forceSize = $this->getSetting(self::PARAM_RECEIVE_FORCE_SIZE) ? MSG_NOERROR : 0;

        return $blockReceive | $modeExcept | $forceSize;
    }

    /**
     * Check whether block or not on reception
     * @return int
     */
    private function getParamBlockReceive()
    {
        return $this->getSetting(self::PARAM_BLOCK_RECEIVE) ? 0 : MSG_IPC_NOWAIT;
    }
}
