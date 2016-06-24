<?php
/**
 * HtmlCompress.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Dispatcher\Plugin;


class HtmlCompressTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $response = $this->getMockBuilder(\FMUP\Response::class)->setMethods(array('getBody', 'setBody'))->getMock();
        $response->expects($this->once())->method('getBody')->willReturn(<<<BODY
<html>
    <head>
        <script type='text/javascript' src='/source.js'></script>
                        </head>
    <body>
        <div>

            <span>
                <img src='img.png' />
            </span>

        </div>


    </body>
</html>
BODY
        );
        $compressedBody = <<<BODY
<html><head><script type='text/javascript' src='/source.js'></script></head><body><div><span><img src='img.png' /></span></div></body></html>
BODY;
        $response->expects($this->once())->method('setBody')->with($compressedBody);
        $htmlCompress = $this->getMockBuilder(\FMUP\Dispatcher\Plugin\HtmlCompress::class)
            ->setMethods(array('getResponse'))
            ->getMock();
        $htmlCompress->method('getResponse')->willReturn($response);
        /** @var $htmlCompress \FMUP\Dispatcher\Plugin\HtmlCompress */
        $htmlCompress->handle();
    }
}
