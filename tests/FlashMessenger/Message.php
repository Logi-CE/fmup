<?php
/**
 * Message.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\FlashMessenger;


class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $message = new \FMUP\FlashMessenger\Message('message');
        $this->assertSame('message', $message->getMessage());
        $this->assertSame($message, $message->setMessage(null));
        $this->assertTrue(is_string($message->getMessage()));
        $this->assertSame('', $message->getMessage());
        $this->assertSame(\FMUP\FlashMessenger\Message::TYPE_DEFAULT, $message->getType());
        $this->assertSame($message, $message->setType('unavailableType'));
        $this->assertSame(\FMUP\FlashMessenger\Message::TYPE_DEFAULT, $message->getType());
        $this->assertSame($message, $message->setType(\FMUP\FlashMessenger\Message::TYPE_DANGER));
        $this->assertSame(\FMUP\FlashMessenger\Message::TYPE_DANGER, $message->getType());
    }
}
