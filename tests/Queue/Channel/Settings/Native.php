<?php
/**
 * Native.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Queue\Channel\Settings;


class NativeTest extends \PHPUnit_Framework_TestCase
{
    public function testDefineByArrayFail()
    {
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Setting is not defined');
        new \FMUP\Queue\Channel\Settings\Native(array('test' => 1));
    }

    public function testDefineByArray()
    {
        $settings = new \FMUP\Queue\Channel\Settings\Native();
        $this->assertSame(
            $settings,
            $settings->defineByArray(
                array(
                    \FMUP\Queue\Channel\Settings\Native::PARAM_RECEIVE_FORCE_SIZE => true,
                    \FMUP\Queue\Channel\Settings\Native::PARAM_RECEIVE_MODE_EXCEPT => true,
                )
            )
        );
        $this->assertTrue($settings->getReceiveForceSize());
        $this->assertSame($settings, $settings->setReceiveForceSize(false));
        $this->assertFalse($settings->getReceiveForceSize());

        $this->assertTrue($settings->getReceiveModeExcept());
        $this->assertSame($settings, $settings->setReceiveModeExcept(false));
        $this->assertFalse($settings->getReceiveModeExcept());
    }
}
