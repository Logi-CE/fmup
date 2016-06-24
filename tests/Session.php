<?php
/**
 * Session.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;

use FMUP\Session;

class SessionMock extends Session
{
    public function __construct()
    {

    }
}

class SapiMockSession extends \FMUP\Sapi
{
    public function __construct()
    {

    }
}

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $reflection = new \ReflectionMethod(\FMUP\Session::class, '__construct');
        $this->assertTrue($reflection->isPrivate());

        $reflection = new \ReflectionMethod(\FMUP\Session::class, '__clone');
        $this->assertTrue($reflection->isPrivate());

        $reflector = new \ReflectionClass(\FMUP\Session::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Session::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Session::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }
    }

    public function testGetInstance()
    {
        $session = Session::getInstance();
        $this->assertInstanceOf(\FMUP\Session::class, $session);
        $session2 = Session::getInstance();
        $this->assertSame($session2, $session);
    }

    public function testRegenerateFails()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('isStarted'))->getMock();
        $session->method('isStarted')->willReturn(false);
        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);
        /** @var $session \FMUP\Session */
        $this->assertFalse($session->regenerate());
    }

    public function testRegenerateFailsWhenSessionStart()
    {
        $session = $this->getMockBuilder(SessionMock::class)
            ->setMethods(array('isStarted', 'sessionRegenerateId'))
            ->getMock();
        $session->method('isStarted')->willReturn(true);
        $session->expects($this->exactly(1))->method('sessionRegenerateId')->willReturn(false);
        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);
        /** @var $session \FMUP\Session */
        $this->assertFalse($session->regenerate());
    }

    public function testSetNameWhenSessionIsStarted()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('isStarted', 'sessionName'))->getMock();
        $session->method('isStarted')->willReturn(true);
        $session->method('sessionName')->willReturn(uniqid());

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertSame($session, $session->setName('test'));
        $this->assertNotSame('test', $session->getName());
    }

    public function testSetName()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('isStarted'))->getMock();
        $session->method('isStarted')->willReturn(false);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertSame($session, $session->setName('test'));
        $this->assertSame('test', $session->getName());
    }

    public function testSetNameFails()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('isStarted'))->getMock();
        $session->method('isStarted')->willReturn(false);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Session name could not contain only numbers');
        /** @var $session \FMUP\Session */
        $session->setName('123');
    }

    public function testSetIdWhenSessionIsStarted()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('isStarted', 'sessionId'))->getMock();
        $session->method('isStarted')->willReturn(true);
        $session->method('sessionId')->willReturn(uniqid());

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertSame($session, $session->setId('test'));
        $this->assertNotSame('test', $session->getId());
    }

    public function testSetId()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('isStarted', 'sessionId'))->getMock();
        $session->method('isStarted')->willReturn(false);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertSame($session, $session->setId('test'));
        $this->assertSame('test', $session->getId());
    }

    public function testSetIdWhenSessionIsNotValid()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('isStarted', 'sessionId'))->getMock();
        $session->method('isStarted')->willReturn(false);
        $session->method('sessionId')->willReturn(uniqid());

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $invalidNames = array(
            'test.name',
            '0123456789!',
            '01234è56789',
            null,
            '',
        );
        $string = '';
        $charDb = 'abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY,-0123456789';
        for ($i = 0; $i < 129; $i++) {
            $string .= $charDb[rand() % strlen($charDb)];
        }
        $invalidNames[] = $string;

        /** @var $session \FMUP\Session */
        foreach ($invalidNames as $invalidName) {
            try {
                $this->assertSame($session, $session->setId($invalidName));
                $this->fail('Session must not be valid : ' . $invalidName);
            } catch (\FMUP\Exception $e) {
                $this->assertSame('Session name is not valid', $e->getMessage());
            }
        }
    }

    public function testIsStartedFailsPhp53()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('phpVersion', 'sessionId'))->getMock();
        $session->method('phpVersion')->willReturn('5.3.0');
        $session->method('sessionId')->willReturn('');

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertFalse($session->isStarted());
    }

    public function testIsStartedFailsPhp54()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('phpVersion', 'sessionStatus'))->getMock();
        $session->method('phpVersion')->willReturn('5.4.0');
        $session->method('sessionStatus')->willReturn('');

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertFalse($session->isStarted());
    }

    public function testIsStartedSucceedPhp53()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('phpVersion', 'sessionId'))->getMock();
        $session->method('phpVersion')->willReturn('5.3.0');
        $session->method('sessionId')->willReturn(uniqid());

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertTrue($session->isStarted());
    }

    public function testIsStartedSucceedPhp54()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('phpVersion', 'sessionStatus'))->getMock();
        $session->method('phpVersion')->willReturn('5.4.0');
        $session->method('sessionStatus')->willReturn(PHP_SESSION_ACTIVE);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        /** @var $session \FMUP\Session */
        $this->assertTrue($session->isStarted());
    }

    public function testSetGetAllWhenSessionStart()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('start'))->getMock();
        $session->method('start')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $sessions = array(
            array(
                'test' => 1,
            ),
            array(
                '1' => 1,
            ),
        );
        /** @var $session \FMUP\Session */
        foreach ($sessions as $array) {
            $this->assertSame($session, $session->setAll($array));
            $this->assertSame($array, $session->getAll());
        }
    }

    public function testSetGetAllWhenSessionFails()
    {
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('start'))->getMock();
        $session->method('start')->willReturn(false);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $sessions = array(
            array(
                'test' => 1,
            ),
            array(
                '1' => 1,
            ),
        );
        /** @var $session \FMUP\Session */
        foreach ($sessions as $array) {
            $this->assertSame($session, $session->setAll($array));
            $this->assertSame(array(), $session->getAll());
        }
    }

    public function testStart()
    {
        $sapi = $this->getMockBuilder(SapiMockSession::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->willReturn(\FMUP\Sapi::CGI);
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('sessionStart'))->getMock();
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        /** @var $session \FMUP\Session */
        $session->setSapi($sapi);
        $this->assertTrue($session->start());
    }

    public function testStartFailsWhenCli()
    {
        $sapi = $this->getMockBuilder(SapiMockSession::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->willReturn(\FMUP\Sapi::CLI);
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('sessionStart'))->getMock();
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        /** @var $session \FMUP\Session */
        $session->setSapi($sapi);
        $this->assertFalse($session->start());
    }

    public function testStartAndSessionId()
    {
        $sapi = $this->getMockBuilder(SapiMockSession::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->willReturn(\FMUP\Sapi::CGI);
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('sessionStart'))->getMock();
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);
        /** @var $session \FMUP\Session */
        $session->setSapi($sapi);
        $this->assertTrue($session->setId('id')->start());
        $this->assertSame('id', $session->getId());
    }

    public function testStartAndSessionName()
    {
        $sapi = $this->getMockBuilder(SapiMockSession::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->willReturn(\FMUP\Sapi::CGI);
        $session = $this->getMockBuilder(SessionMock::class)->setMethods(array('sessionStart'))->getMock();
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        /** @var $session \FMUP\Session */
        $session->setSapi($sapi);
        $this->assertTrue($session->setName('id')->start());
        $this->assertSame('id', $session->getName());
    }
}
