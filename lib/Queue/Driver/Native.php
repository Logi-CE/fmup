<?php
namespace FMUP\Queue\Driver;

use \FMUP\Queue\DriverInterface;
use \FMUP\Queue\Exception;

class Native implements DriverInterface
{
    const MAX_MESSAGE_SIZE = 512;

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
     * /!\ This method will block process while no message is retrieved
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
        $success = msg_receive($queueResource, $messageType, $receivedMessageType, self::MAX_MESSAGE_SIZE, $message, true, 0, $error);
        if (!$success) {
            throw new Exception("Error while receiving message", $error);
        }
        return $message;
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
        $success = msg_send($queueResource, $messageType, $message, true, true, $error);
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
}
