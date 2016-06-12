<?php
/**
 * Download.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Controller\Helper;

use FMUP\Logger;

class MockFail
{
    use \FMUP\Controller\Helper\Download;
}

class Mock extends \FMUP\Controller implements \FMUP\Logger\LoggerInterface
{
    private $response;
    use \FMUP\Controller\Helper\Download;
    use \FMUP\Logger\LoggerTrait;

    public function __construct()
    {
    }

    public function hasResponse()
    {
        return true;
    }

    public function getResponse()
    {
        if (!$this->response) {
            $this->response = new \FMUP\Response();
        }
        return $this->response;
    }
}

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    public function testDownloadHeadersFailsWhenNotController()
    {
        $this->setExpectedException('\FMUP\Exception', 'Unable to use Download trait');
        (new MockFail())->downloadHeaders('text/html');
    }

    public function testDownloadFailsWhenNotController()
    {
        $this->setExpectedException('\FMUP\Exception', 'Unable to use Download trait');
        (new MockFail())->download('/not/existing/path');
    }

    public function testDownloadFailsWhenFileNotExists()
    {
        $this->setExpectedException('\FMUP\Exception\Status\NotFound', 'Unable to find requested file');
        $logger = $this->getMockBuilder('\FMUP\Logger')->setMethods(array('log'))->getMock();
        $logger->expects($this->exactly(1))
            ->method('log')
            ->with(
                $this->equalTo(\FMUP\Logger\Channel\System::NAME),
                $this->equalTo(\FMUP\Logger::ERROR),
                $this->equalTo('Unable to find requested file'),
                $this->equalTo(array('filePath' => '/not/existing/path'))
            );
        /** @var \FMUP\Logger $logger */
        (new Mock())->setLogger($logger)->download('/not/existing/path');
    }

    public function testDownload()
    {
        $file = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', 'composer.json'));
        $expectedContent = file_get_contents($file);
        $response = $this->getMockBuilder('\FMUP\Response')->setMethods(array('send', 'clearHeader'))->getMock();
        $response->expects($this->exactly(1))->method('send');
        $response->expects($this->exactly(1))->method('clearHeader');
        $mock = $this->getMockBuilder('\Tests\Controller\Helper\Mock')->setMethods(array('downloadHeaders', 'getResponse', 'obFlush'))->getMock();
        $mock->expects($this->exactly(1))->method('downloadHeaders')->willReturn($response);
        $mock->method('getResponse')->willReturn($response);
        /** @var $mock Mock */
        $this->expectOutputString($expectedContent);
        $mock->download($file);
    }

    public function testDownloadHeaders()
    {
        $this->assertInstanceOf('\FMUP\Response', (new Mock)->downloadHeaders('text/html'));
    }
}
