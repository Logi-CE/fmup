<?php
/**
 * HtmlCompress.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Dispatcher\Plugin;


class HtmlCompressTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $response = $this->getMock(\FMUP\Response::class, array('getBody', 'setBody'));
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
        $htmlCompress = $this->getMock(\FMUP\Dispatcher\Plugin\HtmlCompress::class, array('getResponse'));
        $htmlCompress->method('getResponse')->willReturn($response);
        /** @var $htmlCompress \FMUP\Dispatcher\Plugin\HtmlCompress */
        $htmlCompress->handle();
    }
}
