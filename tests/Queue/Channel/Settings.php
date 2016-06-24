<?php
/**
 * Settings.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Queue\Channel;


class SettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testDefineByArrayFail()
    {
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Setting is not defined');
        new \FMUP\Queue\Channel\Settings(array('test' => 1));
    }

    public function testDefineByArray()
    {
        $settings = new \FMUP\Queue\Channel\Settings();
        $this->assertSame(
            $settings,
            $settings->defineByArray(
                array(
                    \FMUP\Queue\Channel\Settings::PARAM_MAX_MESSAGE_SIZE => 1,
                    \FMUP\Queue\Channel\Settings::PARAM_BLOCK_SEND => true,
                    \FMUP\Queue\Channel\Settings::PARAM_SERIALIZE => true,
                    \FMUP\Queue\Channel\Settings::PARAM_BLOCK_RECEIVE => true,
                    \FMUP\Queue\Channel\Settings::PARAM_MAX_SEND_RETRY_TIME => 10,
                    \FMUP\Queue\Channel\Settings::PARAM_AUTO_ACK => true,
                    \FMUP\Queue\Channel\Settings::PARAM_CONSUMER_NAME => 'consumerName',
                )
            )
        );
        $this->assertSame(1, $settings->getMaxMessageSize());
        $this->assertSame($settings, $settings->setMaxMessageSize(12));
        $this->assertSame(12, $settings->getMaxMessageSize());

        $this->assertTrue($settings->getBlockSend());
        $this->assertSame($settings, $settings->setBlockSend(false));
        $this->assertFalse($settings->getBlockSend());

        $this->assertTrue($settings->getSerialize());
        $this->assertSame($settings, $settings->setSerialize(false));
        $this->assertFalse($settings->getSerialize());

        $this->assertTrue($settings->getBlockReceive());
        $this->assertSame($settings, $settings->setBlockReceive(false));
        $this->assertFalse($settings->getBlockReceive());

        $this->assertTrue($settings->getAutoAck());
        $this->assertSame($settings, $settings->setAutoAck(false));
        $this->assertFalse($settings->getAutoAck());

        $this->assertSame(10, $settings->getMaxSendRetryTime());
        $this->assertSame($settings, $settings->setMaxSendRetryTime(0));
        $this->assertSame(0, $settings->getMaxSendRetryTime());

        $this->assertSame('consumerName', $settings->getConsumerName());
        $this->assertSame($settings, $settings->setConsumerName());
        $this->assertSame('', $settings->getConsumerName());
    }
}
