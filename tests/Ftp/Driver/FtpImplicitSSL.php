<?php
/**
 * @author jyamin
 */

namespace Tests\Ftp\Driver;


use FMUP\Ftp;


class FtpImplicitSSLTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCurlOptions()
    {
        $method = new \ReflectionMethod('\FMUP\Ftp\Driver\FtpImplicitSSL', 'getCurlOptions');
        $method->setAccessible(true);

        $ftps = new Ftp\Driver\FtpImplicitSSL();
        $this->assertSame(array(
            CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
            CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ), $method->invoke($ftps));

        $ftps = new Ftp\Driver\FtpImplicitSSL(array(
            Ftp\Driver\FtpImplicitSSL::CURL_OPTIONS => array(
                CURLOPT_VERBOSE => true,
            ),
        ));
        $this->assertSame(array(
            CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
            CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_VERBOSE => true,
        ), $method->invoke($ftps));
    }

    public function testGetPassiveMode()
    {
        $method = new \ReflectionMethod('\FMUP\Ftp\Driver\FtpImplicitSSL', 'getPassiveMode');
        $method->setAccessible(true);

        $ftps = new Ftp\Driver\FtpImplicitSSL();
        $this->assertFalse($method->invoke($ftps));

        $ftps = new Ftp\Driver\FtpImplicitSSL(array(Ftp\Driver\FtpImplicitSSL::PASSIVE_MODE => true));
        $this->assertTrue($method->invoke($ftps));
    }

    public function testGetSetUrl()
    {
        $methodGet = new \ReflectionMethod('\FMUP\Ftp\Driver\FtpImplicitSSL', 'getUrl');
        $methodGet->setAccessible(true);

        $methodSet = new \ReflectionMethod('\FMUP\Ftp\Driver\FtpImplicitSSL', 'setUrl');
        $methodSet->setAccessible(true);

        $ftps = new Ftp\Driver\FtpImplicitSSL();
        $this->assertNull($methodGet->invoke($ftps));

        $methodSet->invoke($ftps, 'ftps://url');
        $this->assertSame('ftps://url', $methodGet->invoke($ftps));
    }

    public function testConnectFailInitSession()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'setSession',
                'phpCurlInit',
                'getSession',
                'log',
                'getSettings',
            ))->getMock();

        $resource = fopen('php://stdin', 'r');
        $ftps->expects($this->once())
            ->method('phpCurlInit')
            ->willReturn($resource);

        $ftps->expects($this->once())
            ->method('setSession')
            ->with($resource);

        $ftps->expects($this->once())
            ->method('getSession')
            ->willReturn(null);

        $ftps->expects($this->once())
            ->method('getSettings')
            ->willReturn(array());

        $ftps->expects($this->once())
            ->method('log')
            ->with(\FMUP\Logger::ERROR, 'Could not initialize cURL', array());

        $this->setExpectedException('\FMUP\Ftp\Exception', 'Could not initialize cURL');

        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $ftps->connect('host');
    }

    public function testConnectionSuccessActiveMode()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->setMethods(array(
                'setSession',
                'phpCurlInit',
                'getSession',
                'getPassiveMode',
                'setUrl',
                'log',
            ))->getMock();

        $resource = fopen('php://stdin', 'r');
        $ftps->expects($this->once())
            ->method('phpCurlInit')
            ->willReturn($resource);

        $ftps->expects($this->once())
            ->method('setSession')
            ->with($resource);

        $ftps->expects($this->once())
            ->method('getSession')
            ->willReturn($resource);

        $ftps->expects($this->never())
            ->method('log');

        $ftps->expects($this->once())
            ->method('setUrl')
            ->with('ftps://host/');

        $ftps->expects($this->once())
            ->method('getPassiveMode')
            ->willReturn(false);

        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $ftps->connect('host');

        $settings = new \ReflectionMethod('\FMUP\Ftp\Driver\FtpImplicitSSL', 'getSettings');
        $settings->setAccessible(true);

        $this->assertSame(array(
            Ftp\Driver\FtpImplicitSSL::CURL_OPTIONS => array(
                CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
                CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_PORT => 990,
                CURLOPT_FTPPORT => '-',
            ),
        ), $settings->invoke($ftps));
    }

    public function testConnectionSuccessPassiveMode()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->setMethods(array(
                'setSession',
                'phpCurlInit',
                'getSession',
                'getPassiveMode',
                'setUrl',
                'log',
            ))->getMock();

        $resource = fopen('php://stdin', 'r');
        $ftps->expects($this->once())
            ->method('phpCurlInit')
            ->willReturn($resource);

        $ftps->expects($this->once())
            ->method('setSession')
            ->with($resource);

        $ftps->expects($this->once())
            ->method('getSession')
            ->willReturn($resource);

        $ftps->expects($this->never())
            ->method('log');

        $ftps->expects($this->once())
            ->method('setUrl')
            ->with('ftps://host/');

        $ftps->expects($this->once())
            ->method('getPassiveMode')
            ->willReturn(true);


        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $ftps->connect('host');

        $settings = new \ReflectionMethod('\FMUP\Ftp\Driver\FtpImplicitSSL', 'getSettings');
        $settings->setAccessible(true);

        $this->assertSame(array(
            Ftp\Driver\FtpImplicitSSL::CURL_OPTIONS => array(
                CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
                CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_PORT => 990,
            ),
        ), $settings->invoke($ftps));
    }

    public function testLoginFail()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->setMethods(array(
                'getCurlOptions',
                'phpCurlSetOpt',
                'getSettings',
                'getSession',
                'log',
            ))->getMock();

        $resource = fopen('php://stdin', 'r');

        $ftps->expects($this->once())
            ->method('getCurlOptions')
            ->willReturn(array(
                CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
                CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_PORT => 990,
            ));

        $ftps->expects($this->once())
            ->method('getSettings')
            ->willReturn(array());

        $ftps->expects($this->once())
            ->method('getSession')
            ->willReturn($resource);

        $ftps->expects($this->once())
            ->method('phpCurlSetOpt')
            ->with($resource, CURLOPT_FTP_SSL, CURLFTPSSL_ALL)
            ->willReturn(false);

        $ftps->expects($this->once())
            ->method('log')
            ->with(\FMUP\Logger::ERROR, 'Unable to set cURL option : ' . CURLOPT_FTP_SSL, array());

        $this->setExpectedException('\FMUP\Ftp\Exception', 'Unable to set cURL option : ' . CURLOPT_FTP_SSL);

        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $ftps->login('user', 'pass');
    }

    public function testLoginSuccess()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->setMethods(array(
                'getCurlOptions',
                'phpCurlSetOpt',
                'getSession',
                'log',
            ))->getMock();

        $resource = fopen('php://stdin', 'r');

        $ftps->expects($this->once())
            ->method('getCurlOptions')
            ->willReturn(array(
                CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
                CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
            ));

        $ftps->expects($this->exactly(2))
            ->method('getSession')
            ->willReturn($resource);

        $ftps->expects($this->exactly(2))
            ->method('phpCurlSetOpt')
            ->withConsecutive(
                array($resource, CURLOPT_FTP_SSL, CURLFTPSSL_ALL),
                array($resource, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_DEFAULT)
            )->willReturn(true);

        $ftps->expects($this->never())
            ->method('log');

        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $this->assertTrue($ftps->login('user', 'pass'));

        $settings = new \ReflectionMethod('\FMUP\Ftp\Driver\FtpImplicitSSL', 'getSettings');
        $settings->setAccessible(true);

        $this->assertSame(array(
            Ftp\Driver\FtpImplicitSSL::CURL_OPTIONS => array(
                CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
                CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERPWD => 'user:pass',
            ),
        ), $settings->invoke($ftps));
    }

    public function testGetFail()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->setMethods(array(
                'phpFopen',
                'getSettings',
                'log',
            ))->getMock();

        $ftps->expects($this->once())
            ->method('phpFopen')
            ->willReturn(false);

        $ftps->expects($this->once())
            ->method('getSettings')
            ->willReturn(array());

        $ftps->expects($this->once())
            ->method('log')
            ->with(\FMUP\Logger::ERROR, 'Unable to open file to write : local_file', array());

        $this->setExpectedException('\FMUP\Ftp\Exception', 'Unable to open file to write : local_file');

        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $ftps->get('local_file', 'remote_file');
    }

    public function testGetSuccess()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->setMethods(array(
                'phpFopen',
                'log',
                'getSession',
                'phpCurlSetOpt',
                'getUrl',
                'phpCurlExec',
                'phpFclose',
            ))->getMock();

        $resource = fopen('php://stdin', 'r');
        $resource2 = fopen('php://stdin', 'r');

        $ftps->expects($this->once())
            ->method('phpFopen')
            ->willReturn($resource);

        $ftps->expects($this->never())
            ->method('log');

        $ftps->expects($this->exactly(6))
            ->method('getSession')
            ->willReturn($resource2);

        $ftps->expects($this->once())
            ->method('getUrl')
            ->willReturn('ftps://url/');

        $ftps->expects($this->exactly(5))
            ->method('phpCurlSetOpt')
            ->withConsecutive(
                array($resource2, CURLOPT_URL, 'ftps://url/remote_file'),
                array($resource2, CURLOPT_FOLLOWLOCATION, 1),
                array($resource2, CURLOPT_RETURNTRANSFER, 1),
                array($resource2, CURLOPT_UPLOAD, false),
                array($resource2, CURLOPT_FILE, $resource)
            )
            ->willReturn(true);

        $ftps->expects($this->once())
            ->method('phpCurlExec')
            ->with($resource2)
            ->willReturn(true);

        $ftps->expects($this->once())
            ->method('phpFclose')
            ->with($resource);

        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $this->assertTrue($ftps->get('local_file', 'remote_file'));
    }

    public function testDelete()
    {
        $ftps = new Ftp\Driver\FtpImplicitSSL();
        $this->assertFalse($ftps->delete('test'));
    }

    public function testClose()
    {
        $ftps = $this->getMockBuilder('\FMUP\Ftp\Driver\FtpImplicitSSL')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'phpCurlClose',
                'getSession',
            ))
            ->getMock();

        $resource = fopen('php://stdin', 'r');
        $ftps->expects($this->once())
            ->method('getSession')
            ->willReturn($resource);
        $ftps->expects($this->once())
            ->method('phpCurlClose')
            ->with($resource);

        /** @var Ftp\Driver\FtpImplicitSSL $ftps */
        $this->assertTrue($ftps->close());
    }
}
