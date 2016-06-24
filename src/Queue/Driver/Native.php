<?php
namespace FMUP\Queue\Driver;

use FMUP\Environment;
use FMUP\Queue\Channel;
use FMUP\Queue\DriverInterface;
use FMUP\Queue\Exception;
use FMUP\Queue\Message;

class Native implements DriverInterface, Environment\OptionalInterface
{
    use Environment\OptionalTrait;

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

    /**
     * Connect to specified queue
     * @param Channel $channel
     * @return Channel
     * @throws Exception
     */
    public function connect(Channel $channel)
    {
        if (!$channel->hasResource()) {
            $name = $this->secureQueueName($channel->getName());
            $channel->setName($name);
            $resource = $this->msgGetQueue($name);
            $channel->setResource($resource);
        }
        return $channel;
    }

    /**
     * @param string $name
     * @return resource
     * @codeCoverageIgnore
     */
    protected function msgGetQueue($name)
    {
        return msg_get_queue($name);
    }

    /**
     * Check if queue exists
     * @param Channel $channel
     * @return bool
     * @throws Exception
     */
    public function exists(Channel $channel)
    {
        $name = (!$channel->hasResource()) ? $this->secureQueueName($channel->getName()) : $channel->getName();
        return $this->msgQueueExists($name);
    }

    /**
     * @param $name
     * @return bool
     * @codeCoverageIgnore
     */
    protected function msgQueueExists($name)
    {
        return msg_queue_exists($name);
    }

    /**
     * Get a message from queue
     *
     * @param Channel $channel
     * @param string $messageType
     * @return mixed|null
     * @throws Exception
     */
    public function pull(Channel $channel, $messageType = null)
    {
        if (!$channel->hasResource()) {
            $this->connect($channel);
        }
        $messageType = $this->secureMessageType($messageType);
        $receivedMessageType = 0;
        $message = null;
        $error = 0;
        $messageSize = $this->getMessageSize($channel);
        $success = $this->msgReceive(
            $channel->getResource(),
            $messageType,
            $receivedMessageType,
            $messageSize,
            $message,
            $channel->getSettings()->getSerialize(),
            $this->getReceiveFlags($channel),
            $error
        );
        $isNonBlockReceive = (MSG_IPC_NOWAIT & $this->getParamBlockReceive($channel));
        $isNonBlockingPlusNoMessage = $isNonBlockReceive && ($error === MSG_ENOMSG);
        if (!$success && !$isNonBlockingPlusNoMessage) {
            throw new Exception("Error while receiving message", $error);
        }
        return $message ? (new Message())->setOriginal($message)->setTranslated($message) : null;
    }

    /**
     * @param $queue
     * @param $desiredMsgType
     * @param $msgType
     * @param $maxsize
     * @param $message
     * @param bool|true $unSerialize
     * @param int $flags
     * @param null $errorCode
     * @return bool
     * @codeCoverageIgnore
     */
    protected function msgReceive(
        $queue,
        $desiredMsgType,
        &$msgType,
        $maxsize,
        &$message,
        $unSerialize = true,
        $flags = 0,
        &$errorCode = null
    ) {
        return msg_receive($queue, $desiredMsgType, $msgType, $maxsize, $message, $unSerialize, $flags, $errorCode);
    }

    /**
     * Retrieve message maximum size for a given queue
     * @param Channel $channel
     * @return int
     */
    private function getMessageSize(Channel $channel)
    {
        $messageSize = $channel->getSettings()->getMaxMessageSize();
        if (!$messageSize) {
            $configuration = $this->getConfiguration($channel->getResource());
            $messageSize = (int)$configuration[self::CONFIGURATION_MESSAGE_SIZE];
            $channel->getSettings()->setMaxMessageSize($messageSize);
        }
        return $messageSize;
    }

    /**
     * Push a message in queue
     * @param Channel $channel
     * @param mixed $message
     * @param string|int $messageType
     * @return bool
     * @throws Exception
     */
    public function push(Channel $channel, $message, $messageType = null)
    {
        if (!$channel->hasResource()) {
            $this->connect($channel);
        }
        $messageType = $this->secureMessageType($messageType);
        $error = 0;
        $blockSend = (bool)$channel->getSettings()->getBlockSend();
        $maxSendRetry = (int)$channel->getSettings()->getMaxSendRetryTime();
        $serialize = (bool)$channel->getSettings()->getSerialize();
        $retry = 0;
        $success = false;
        while ($retry < $maxSendRetry) {
            $success = $this->msgSend($channel->getResource(), $messageType, $message, $serialize, $blockSend, $error);
            $retry = ($success || (!$success && $error != MSG_EAGAIN)) ? $maxSendRetry : $retry + 1;
        }
        if (!$success) {
            throw new Exception("Error while sending message", $error);
        }
        return $success;
    }

    /**
     * @param $queue
     * @param $msgType
     * @param $message
     * @param bool|true $serialize
     * @param bool|true $blocking
     * @param null $errorCode
     * @return bool
     * @codeCoverageIgnore
     */
    protected function msgSend($queue, $msgType, $message, $serialize = true, $blocking = true, &$errorCode = null)
    {
        return msg_send($queue, $msgType, $message, $serialize, $blocking, $errorCode);
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
            throw new Exception('Queue name must be in INT > 0 to use semaphores');
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
            throw new Exception('Message Type must be in INT > 0 to use semaphores');
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
            return (int)$string;
        }
        if ($this->hasEnvironment()) {
            $string .= $this->getEnvironment()->get();
        }
        $length = strlen($string);
        $return = 0;
        for ($i = 0; $i < $length; $i++) {
            $return += ord($string{$i});
        }
        return (int)($length . '0' . $return);
    }

    /**
     * Get queue configuration
     * @param resource $queueResource
     * @return array
     * @codeCoverageIgnore
     */
    protected function getConfiguration($queueResource)
    {
        return msg_stat_queue($queueResource);
    }

    /**
     * Define queue configuration
     * @param Channel $channel
     * @param array $params
     * @return bool
     */
    public function setConfiguration(Channel $channel, $params)
    {
        if (isset($params[self::CONFIGURATION_MESSAGE_SIZE])) {
            $channel->getSettings()->setMaxMessageSize((int)$params[self::CONFIGURATION_MESSAGE_SIZE]);
        }
        return $this->msgSetQueue($channel->getResource(), (array)$params);
    }

    /**
     * @param $queue
     * @param array $data
     * @return bool
     * @codeCoverageIgnore
     */
    protected function msgSetQueue($queue, array $data)
    {
        return msg_set_queue($queue, $data);
    }

    /**
     * Destroy a queue
     * @param Channel $channel
     * @return bool
     */
    public function destroy(Channel $channel)
    {
        if ($channel->hasResource()) {
            if ($this->msgRemoveQueue($channel->getResource())) {
                $channel->setResource(null);
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * @param $queue
     * @return bool
     * @codeCoverageIgnore
     */
    protected function msgRemoveQueue($queue)
    {
        return msg_remove_queue($queue);
    }

    /**
     * Reception options
     * @param Channel $channel
     * @return int
     */
    private function getReceiveFlags(Channel $channel)
    {
        $modeExcept = 0;
        $forceSize = 0;
        $settings = $channel->getSettings();
        if ($settings instanceof Channel\Settings\Native) {
            $modeExcept = $settings->getReceiveModeExcept() ? MSG_EXCEPT : 0;
            $forceSize = $settings->getReceiveForceSize() ? MSG_NOERROR : 0;
        }
        $blockReceive = $this->getParamBlockReceive($channel);

        return $blockReceive | $modeExcept | $forceSize;
    }

    /**
     * Check whether block or not on reception
     * @param Channel $channel
     * @return int
     */
    private function getParamBlockReceive(Channel $channel)
    {
        return $channel->getSettings()->getBlockReceive() ? 0 : MSG_IPC_NOWAIT;
    }

    /**
     * @todo factorize this method
     * @param Channel $channel
     * @return array
     */
    public function getStats(Channel $channel)
    {
        if (!$channel->hasResource()) {
            $this->connect($channel);
        }
        return $this->getConfiguration($channel->getResource());
    }

    /**
     * This methods do nothing since messages are auto-acked in SystemV. Sorry :(
     * @param Channel $channel
     * @param Message $message
     * @return $this
     */
    public function ackMessage(Channel $channel, Message $message)
    {
        return $this;
    }
}
