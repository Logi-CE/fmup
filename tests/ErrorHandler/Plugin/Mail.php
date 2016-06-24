<?php
/**
 * Mail.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\ErrorHandler\Plugin;

class SapiMockMail extends \FMUP\Sapi
{
    public function __construct()
    {

    }
}

class MailTest extends \PHPUnit_Framework_TestCase
{
    public function testCanHandle()
    {
        $config = $this->getMockBuilder(\FMUP\Config::class)->setMethods(array('get'))->getMock();
        $config->method('get')->will($this->onConsecutiveCalls(true, false, true, false, true, false));
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->setMethods(array('getConfig'))->getMock();
        $bootstrap->method('getConfig')->willReturn($config);
        $mail = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Mail::class)->setMethods(array('iniGet'))->getMock();
        $mail->method('iniGet')->will($this->onConsecutiveCalls(true, false, true, false, true, false));
        /**
         * @var $mail \FMUP\ErrorHandler\Plugin\Mail
         * @var $bootstrap \FMUP\Bootstrap
         */
        $mail->setBootstrap($bootstrap);
        $this->assertInstanceOf(\FMUP\ErrorHandler\Plugin\Abstraction::class, $mail);
        $mail->setException(new \FMUP\Exception\Status\NotFound('testMessage'));
        $this->assertFalse($mail->canHandle());
        $mail->setException(new \Exception('testMessage'));
        $this->assertFalse($mail->canHandle());
        $mail->setException(new \FMUP\Exception\Status\NotFound('testMessage'));
        $this->assertFalse($mail->canHandle());
        $mail->setException(new \Exception('testMessage'));
        $this->assertFalse($mail->canHandle());
        $mail->setException(new \FMUP\Exception\Status\NotFound('testMessage'));
        $this->assertFalse($mail->canHandle());
        $mail->setException(new \Exception('testMessage'));
        $this->assertFalse($mail->canHandle());
        $mail->setException(new \FMUP\Exception\Status\NotFound('testMessage'));
        $this->assertFalse($mail->canHandle());
        $mail->setException(new \Exception('testMessage'));
        $this->assertTrue($mail->canHandle());
    }

    public function testHandle()
    {
        $sapi = $this->getMockBuilder(SapiMockMail::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->will($this->onConsecutiveCalls(\FMUP\Sapi::CLI, \FMUP\Sapi::CGI));
        $config = $this->getMockBuilder(\FMUP\Config::class)->setMethods(array('get'))->getMock();
        $config->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    'erreur_mail_sujet',
                    'mail_robot',
                    'mail_robot_name',
                    'mail_support',
                    'mail_robot',
                    'mail_robot_name',
                    'support@support.com,support2@support.com'
                )
            );
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->setMethods(array('getConfig', 'getSapi'))->getMock();
        $bootstrap->method('getConfig')->willReturn($config);
        $bootstrap->method('getSapi')->willReturn($sapi);
        $mail = $this->getMockBuilder(\FMUP\Mail::class)
            ->setMethods(array('Send'))
            ->setConstructorArgs(
                array(
                    $this->getMockBuilder(\FMUP\Config::class)->setMethods(array('get'))->getMock()
                )
            )
            ->getMock();
        $mail->expects($this->exactly(2))->method('Send')->will($this->onConsecutiveCalls(true, false, true, true));
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('getServer'))->getMock();
        $request->method('getServer')->willReturn('TestUnitServer');
        $exceptionMock = $this->getMockBuilder(\stdClass::class)
            ->setMethods(array('getTrace', 'getMessage', 'getLine', 'getFile'))
            ->getMock();
        $exceptionMock->method('getTrace')->willReturn(
            array(
                array(),
                array(
                    'args' => array(
                        fopen(__FILE__, 'r'),
                    )
                ),
            )
        );
        $mailPlugin = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Mail::class)
            ->setMethods(array('createMail', 'getRequest', 'getException'))
            ->getMock();
        $mailPlugin->method('getException')
            ->will(
                $this->onConsecutiveCalls(
                    new \Exception('test unit exception'),
                    new \Exception('test unit exception'),
                    $exceptionMock,
                    $exceptionMock,
                    $exceptionMock
                )
            );
        $mailPlugin->method('createMail')->willReturn($mail);
        $mailPlugin->method('getRequest')->willReturn($request);

        /**
         * @var $mailPlugin \FMUP\ErrorHandler\Plugin\Mail
         * @var $bootstrap \FMUP\Bootstrap
         * @var $exceptionMock \Exception
         */
        $mailPlugin->setBootstrap($bootstrap);
        $this->assertSame($mailPlugin, $mailPlugin->handle());
        $_SERVER["REMOTE_ADDR"] = '127.0.0.1';
        $_SERVER["HTTP_HOST"] = 'HTTP_HOST UNIT TEST';
        $_SERVER["REQUEST_URI"] = 'REQUEST_URI UNIT TEST';
        $this->assertSame($mailPlugin, $mailPlugin->handle());
    }
}
