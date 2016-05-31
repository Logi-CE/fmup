<?php
/**
 * Message.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Queue;


class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetTranslatedOriginal()
    {
        $message = new \FMUP\Queue\Message();
        $this->assertNull($message->getOriginal());
        $this->assertNull($message->getTranslated());
        $this->assertSame($message, $message->setOriginal('original'));
        $this->assertSame('original', $message->getOriginal());
        $this->assertSame($message, $message->setTranslated('translated'));
        $this->assertSame('translated', $message->getTranslated());
    }
}
