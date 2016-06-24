<?php
/**
 * Render.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Dispatcher\Plugin;


class RenderTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $response = $this->getMockBuilder(\FMUP\Response::class)->setMethods(array('send'))->getMock();
        $response->expects($this->once())->method('send');
        /** @var $response \FMUP\Response */
        $render = new \FMUP\Dispatcher\Plugin\Render();
        $this->assertInstanceOf(\FMUP\Dispatcher\Plugin::class, $render);
        $this->assertSame('Render', $render->getName());
        $this->assertSame($render, $render->setResponse($response)->handle());
    }
}
