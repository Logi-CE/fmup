<?php
namespace FMUPTests\Ftp\Driver;

use FMUP\Ftp;

class SftpTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSftpSession()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getSftpSession');
        $method->setAccessible(true);

        $sftp = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(array('ssh2Sftp', 'getSession'))
            ->getMock();
        $sftp->method('ssh2Sftp')->willReturn(fopen('php://stdin', 'r'));
        /**
         * @var $sftp Ftp\Driver\Sftp
         */
        $ret = $method->invoke($sftp);
        $this->assertTrue(is_resource($ret));
        $this->assertSame($ret, $method->invoke($sftp));
    }

    public function testGetMethods()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getMethods');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertNull($method->invoke($sftp));

        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::METHODS => array('key' => 'val')));
        $this->assertTrue(is_array($method->invoke($sftp2)));
        $this->assertSame(array('key' => 'val'), $method->invoke($sftp2));
    }

    public function testGetCallbacks()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getCallbacks');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertNull($method->invoke($sftp));

        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::CALLBACKS => array('key' => 'val')));
        $this->assertTrue(is_array($method->invoke($sftp2)));
        $this->assertSame(array('key' => 'val'), $method->invoke($sftp2));
    }

    public function testGetUseIncludePath()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getUseIncludePath');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertFalse($method->invoke($sftp));

        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::USE_INCLUDE_PATH => true));
        $this->assertTrue($method->invoke($sftp2));
    }

    public function testGetGetContentContext()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getGetContentContext');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertNull($method->invoke($sftp));

        $resource = fopen('php://stdin', 'r');
        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::GET_CONTENT_CONTEXT => $resource));
        $this->assertTrue(is_resource($method->invoke($sftp2)));
        $this->assertSame($resource, $method->invoke($sftp2));
    }

    public function testGetOffset()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getOffset');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertSame(0, $method->invoke($sftp));

        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::OFFSET => 5));
        $this->assertTrue(is_int($method->invoke($sftp2)));
        $this->assertSame(5, $method->invoke($sftp2));
    }

    public function testGetMaxLen()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getMaxLen');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertNull($method->invoke($sftp));

        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::MAXLEN => 500));
        $this->assertTrue(is_int($method->invoke($sftp2)));
        $this->assertSame(500, $method->invoke($sftp2));
    }

    public function testGetPutContentContext()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getPutContentContext');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertNull($method->invoke($sftp));

        $resource = fopen('php://stdin', 'r');
        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::PUT_CONTENT_CONTEXT => $resource));
        $this->assertTrue(is_resource($method->invoke($sftp2)));
        $this->assertSame($resource, $method->invoke($sftp2));
    }

    public function testGetPutContentFlags()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Sftp::class, 'getPutContentFlags');
        $method->setAccessible(true);

        $sftp = new Ftp\Driver\Sftp();
        $this->assertSame(0, $method->invoke($sftp));

        $sftp2 = new Ftp\Driver\Sftp(array(Ftp\Driver\Sftp::PUT_CONTENT_FLAGS => FILE_APPEND));
        $this->assertTrue(is_int($method->invoke($sftp2)));
        $this->assertSame(FILE_APPEND, $method->invoke($sftp2));
    }

    public function testConnect()
    {
        $sftp = $this->getMockBuilder(Ftp\Driver\Sftp::class)->setMethods(array('ssh2Connect'))->getMock();
        $sftp2 = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(array('ssh2Connect'))
            ->setConstructorArgs(array(array(Ftp\Driver\Sftp::METHODS => array('key' => 'val'))))
            ->getMock();
        $sftp3 = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(array('ssh2Connect'))
            ->setConstructorArgs(array(array(Ftp\Driver\Sftp::CALLBACKS => array('key2' => 'val2'))))
            ->getMock();
        $sftp4 = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(array('ssh2Connect'))
            ->setConstructorArgs(
                array(array(
                    Ftp\Driver\Sftp::METHODS => array('key' => 'val'),
                    Ftp\Driver\Sftp::CALLBACKS => array('key2' => 'val2')
                ))
            )
            ->getMock();
        $sftp->expects($this->once())
            ->method('ssh2Connect')
            ->willReturn(fopen('php://stdin', 'r'))
            ->with($this->equalTo('host'), $this->equalTo(22), $this->equalTo(null), $this->equalTo(null));
        $sftp2->expects($this->once())
            ->method('ssh2Connect')
            ->willReturn(fopen('php://stdin', 'r'))
            ->with(
                $this->equalTo('host'),
                $this->equalTo(222),
                $this->equalTo(array('key' => 'val')),
                $this->equalTo(null));
        $sftp3->expects($this->once())
            ->method('ssh2Connect')
            ->willReturn(fopen('php://stdin', 'r'))
            ->with(
                $this->equalTo('host'),
                $this->equalTo(22),
                $this->equalTo(null),
                $this->equalTo(array('key2' => 'val2'))
            );
        $sftp4->expects($this->once())
            ->method('ssh2Connect')
            ->willReturn(fopen('php://stdin', 'r'))
            ->with(
                $this->equalTo('host'),
                $this->equalTo(22),
                $this->equalTo(array('key' => 'val')),
                $this->equalTo(array('key2' => 'val2'))
            );
        /**
         * @var $sftp Ftp\Driver\Sftp
         * @var $sftp2 Ftp\Driver\Sftp
         * @var $sftp3 Ftp\Driver\Sftp
         * @var $sftp4 Ftp\Driver\Sftp
         */
        $ret = $sftp->connect('host', 22);
        $sftp2->connect('host', 222);
        $sftp3->connect('host', 22);
        $sftp4->connect('host', 22);
        $this->assertTrue(is_resource($sftp->getSession()));
        $this->assertTrue(is_resource($sftp2->getSession()));
        $this->assertTrue(is_resource($sftp3->getSession()));
        $this->assertTrue(is_resource($sftp4->getSession()));
        $this->assertSame($sftp, $ret);
    }

    public function testLoginFail()
    {
        $sftp = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(array('ssh2AuthPassword', 'getSession'))
            ->getMock();
        $resource = fopen('php://stdin', 'r');
        $sftp->method('getSession')->willReturn($resource);
        $sftp->expects($this->once())->method('ssh2AuthPassword')->willReturn(false)->with(
            $this->equalTo($resource),
            $this->equalTo('login'),
            $this->equalTo('pass')
        );

        $this->expectException(Ftp\Exception::class);
        $this->expectExceptionMessage('Unable to login to the SFTP server');
        /**
         * @var $sftp Ftp\Driver\Sftp
         */
        $ret = $sftp->login('login', 'pass');
        $this->assertFalse($ret);
    }

    public function testLoginSuccess()
    {
        $sftp = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(array('ssh2AuthPassword', 'getSession'))
            ->getMock();
        $resource = fopen('php://stdin', 'r');
        $sftp->method('getSession')->willReturn($resource);
        $sftp->expects($this->once())->method('ssh2AuthPassword')->willReturn(true)->with(
            $this->equalTo($resource),
            $this->equalTo('login'),
            $this->equalTo('pass')
        );

        /**
         * @var $sftp Ftp\Driver\Sftp
         */
        $ret = $sftp->login('login', 'pass');
        $this->assertTrue($ret);
    }

    public function testGetFileWithoutMaxLen()
    {
        $sftp = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(
                array(
                    'filePutContents',
                    'fileGetContents',
                    'getSftpSession',
                    'getUseIncludePath',
                    'getGetContentContext',
                    'getOffset',
                    'getMaxLen',
                    'getPutContentFlags',
                    'getPutContentContext',
                )
            )
            ->getMock();
        $resourceGetContext = fopen('php://stdin', 'r');
        $resourcePutContext = fopen('php://stdin', 'r');
        $resourceSftpSession = fopen('php://stdin', 'r');
        $sftp->method('getUseIncludePath')->willReturn(false);
        $sftp->method('getGetContentContext')->willReturn($resourceGetContext);
        $sftp->method('getOffset')->willReturn(0);
        $sftp->method('getMaxLen')->willReturn(null);
        $sftp->method('getPutContentFlags')->willReturn(0);
        $sftp->method('getPutContentContext')->willReturn($resourcePutContext);
        $sftp->method('getSftpSession')->willReturn($resourceSftpSession);

        $sftp->expects($this->once())->method('fileGetContents')->willReturn('content of my file')->with(
            'ssh2.sftp://' . $resourceSftpSession . '/path/to/remote/file.txt',
            $this->equalTo(false),
            $this->equalTo($resourceGetContext),
            $this->equalTo(0)
        );
        $sftp->expects($this->once())->method('filePutContents')->willReturn(18)->with(
            $this->equalTo('path/to/local/file.txt'),
            $this->equalTo('content of my file'),
            $this->equalTo(0),
            $this->equalTo($resourcePutContext)
        );
        /**
         * @var $sftp Ftp\Driver\Sftp
         */
        $ret = $sftp->get('path/to/local/file.txt', 'path/to/remote/file.txt');
        $this->assertSame(18, $ret);
    }

    public function testGetFileWithMaxLen()
    {
        $sftp = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(
                array(
                    'filePutContents',
                    'fileGetContents',
                    'getSftpSession',
                    'getUseIncludePath',
                    'getGetContentContext',
                    'getOffset',
                    'getMaxLen',
                    'getPutContentFlags',
                    'getPutContentContext',
                )
            )
            ->getMock();
        $resourceGetContext = fopen('php://stdin', 'r');
        $resourcePutContext = fopen('php://stdin', 'r');
        $resourceSftpSession = fopen('php://stdin', 'r');
        $sftp->method('getUseIncludePath')->willReturn(false);
        $sftp->method('getGetContentContext')->willReturn($resourceGetContext);
        $sftp->method('getOffset')->willReturn(0);
        $sftp->method('getMaxLen')->willReturn(10);
        $sftp->method('getPutContentFlags')->willReturn(0);
        $sftp->method('getPutContentContext')->willReturn($resourcePutContext);
        $sftp->method('getSftpSession')->willReturn($resourceSftpSession);

        $sftp->expects($this->once())->method('fileGetContents')->willReturn('content of my file')->with(
            'ssh2.sftp://' . $resourceSftpSession . '/path/to/remote/file.txt',
            $this->equalTo(false),
            $this->equalTo($resourceGetContext),
            $this->equalTo(0),
            $this->equalTo(10)
        );
        $sftp->expects($this->once())->method('filePutContents')->willReturn(10)->with(
            $this->equalTo('path/to/local/file.txt'),
            $this->equalTo('content of my file'),
            $this->equalTo(0),
            $this->equalTo($resourcePutContext)
        );
        /**
         * @var $sftp Ftp\Driver\Sftp
         */
        $ret = $sftp->get('path/to/local/file.txt', 'path/to/remote/file.txt');
        $this->assertSame(10, $ret);
    }

    public function testDeleteFile()
    {
        $sftp = $this->getMockBuilder(Ftp\Driver\Sftp::class)
            ->setMethods(array('ssh2SftpUnlink', 'getSftpSession'))
            ->getMock();
        $resource = fopen('php://stdin', 'r');
        $sftp->method('getSftpSession')->willReturn($resource);
        $sftp->expects($this->exactly(2))
            ->method('ssh2SftpUnlink')
            ->will($this->onConsecutiveCalls(true, false))
            ->with(
                $this->equalTo($resource),
                $this->equalTo('file')
            );

        /**
         * @var $sftp Ftp\Driver\Sftp
         */
        $ret1 = $sftp->delete('file');
        $ret2 = $sftp->delete('file');
        $this->assertTrue($ret1);
        $this->assertFalse($ret2);
    }

    public function testClose()
    {
        $sftp = new Ftp\Driver\Sftp();

        $this->assertTrue($sftp->close());
    }
}
