<?php
/**
 * Session.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

use FMUP\Session;

if (!class_exists('\Tests\SessionMock')) {
    class SessionMock extends Session
    {
        public function __construct()
        {

        }
    }
}

if (!class_exists('\Tests\SapiMock')) {
    class SapiMock extends \FMUP\Sapi
    {
        public function __construct()
        {

        }
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
        $session = $this->getMock(SessionMock::class, array('isStarted'));
        $session->method('isStarted')->willReturn(false);
        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);
        $this->assertFalse($session->regenerate());
    }

    public function testRegenerateFailsWhenSessionStart()
    {
        $session = $this->getMock(SessionMock::class, array('isStarted', 'sessionRegenerateId'));
        $session->method('isStarted')->willReturn(true);
        $session->expects($this->exactly(1))->method('sessionRegenerateId')->willReturn(false);
        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);
        $this->assertFalse($session->regenerate());
    }

    public function testSetNameWhenSessionIsStarted()
    {
        $session = $this->getMock(SessionMock::class, array('isStarted', 'sessionName'));
        $session->method('isStarted')->willReturn(true);
        $session->method('sessionName')->willReturn(uniqid());

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertSame($session, $session->setName('test'));
        $this->assertNotSame('test', $session->getName());
    }

    public function testSetName()
    {
        $session = $this->getMock(SessionMock::class, array('isStarted'));
        $session->method('isStarted')->willReturn(false);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertSame($session, $session->setName('test'));
        $this->assertSame('test', $session->getName());
    }

    public function testSetNameFails()
    {
        $session = $this->getMock(SessionMock::class, array('isStarted'));
        $session->method('isStarted')->willReturn(false);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);
        $this->setExpectedException(\FMUP\Exception::class, 'Session name could not contain only numbers');
        $session->setName('123');
    }

    public function testSetIdWhenSessionIsStarted()
    {
        $session = $this->getMock(SessionMock::class, array('isStarted', 'sessionId'));
        $session->method('isStarted')->willReturn(true);
        $session->method('sessionId')->willReturn(uniqid());

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertSame($session, $session->setId('test'));
        $this->assertNotSame('test', $session->getId());
    }

    public function testSetId()
    {
        $session = $this->getMock(SessionMock::class, array('isStarted', 'sessionId'));
        $session->method('isStarted')->willReturn(false);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertSame($session, $session->setId('test'));
        $this->assertSame('test', $session->getId());
    }

    public function testSetIdWhenSessionIsNotValid()
    {
        $session = $this->getMock(SessionMock::class, array('isStarted', 'sessionId'));
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
        $session = $this->getMock(SessionMock::class, array('phpVersion', 'sessionId'));
        $session->method('phpVersion')->willReturn('5.3.0');
        $session->method('sessionId')->willReturn('');

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertFalse($session->isStarted());
    }

    public function testIsStartedFailsPhp54()
    {
        $session = $this->getMock(SessionMock::class, array('phpVersion', 'sessionStatus'));
        $session->method('phpVersion')->willReturn('5.4.0');
        $session->method('sessionStatus')->willReturn('');

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertFalse($session->isStarted());
    }

    public function testIsStartedSucceedPhp53()
    {
        $session = $this->getMock(SessionMock::class, array('phpVersion', 'sessionId'));
        $session->method('phpVersion')->willReturn('5.3.0');
        $session->method('sessionId')->willReturn(uniqid());

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertTrue($session->isStarted());
    }

    public function testIsStartedSucceedPhp54()
    {
        $session = $this->getMock(SessionMock::class, array('phpVersion', 'sessionStatus'));
        $session->method('phpVersion')->willReturn('5.4.0');
        $session->method('sessionStatus')->willReturn(PHP_SESSION_ACTIVE);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $this->assertTrue($session->isStarted());
    }

    public function testSetGetAllWhenSessionStart()
    {
        $session = $this->getMock(SessionMock::class, array('start'));
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
        foreach ($sessions as $array) {
            $this->assertSame($session, $session->setAll($array));
            $this->assertSame($array, $session->getAll());
        }
    }

    public function testSetGetAllWhenSessionFails()
    {
        $session = $this->getMock(SessionMock::class, array('start'));
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
        foreach ($sessions as $array) {
            $this->assertSame($session, $session->setAll($array));
            $this->assertSame(array(), $session->getAll());
        }
    }

    public function testStart()
    {
        $sapi = $this->getMock(SapiMock::class, array('get'));
        $sapi->method('get')->willReturn(\FMUP\Sapi::CGI);
        $session = $this->getMock(SessionMock::class, array('sessionStart'));
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        $session->setSapi($sapi);
        $this->assertTrue($session->start());
    }

    public function testStartFailsWhenCli()
    {
        $sapi = $this->getMock(SapiMock::class, array('get'));
        $sapi->method('get')->willReturn(\FMUP\Sapi::CLI);
        $session = $this->getMock(SessionMock::class, array('sessionStart'));
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        $session->setSapi($sapi);
        $this->assertFalse($session->start());
    }

    public function testStartAndSessionId()
    {
        $sapi = $this->getMock(SapiMock::class, array('get'));
        $sapi->method('get')->willReturn(\FMUP\Sapi::CGI);
        $session = $this->getMock(SessionMock::class, array('sessionStart'));
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        $session->setSapi($sapi);
        $this->assertTrue($session->setId('id')->start());
        $this->assertSame('id', $session->getId());
    }

    public function testStartAndSessionName()
    {
        $sapi = $this->getMock(SapiMock::class, array('get'));
        $sapi->method('get')->willReturn(\FMUP\Sapi::CGI);
        $session = $this->getMock(SessionMock::class, array('sessionStart'));
        $session->method('sessionStart')->willReturn(true);

        $reflection = new \ReflectionProperty(\FMUP\Session::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($session);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        $session->setSapi($sapi);
        $this->assertTrue($session->setName('id')->start());
        $this->assertSame('id', $session->getName());
    }
}
