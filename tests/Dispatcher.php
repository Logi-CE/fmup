<?php
/**
 * Dispatcher.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;


class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetOriginalRequest()
    {
        $dispatcher = new \FMUP\Dispatcher();
        $request = $this->getMock(\FMUP\Request\Cli::class, null);
        $this->assertNull($dispatcher->getOriginalRequest());
        $reflection = new \ReflectionMethod(\FMUP\Dispatcher::class, 'setOriginalRequest');
        $reflection->setAccessible(true);
        $this->assertSame($dispatcher, $reflection->invoke($dispatcher, $request));
        $this->assertNotSame($request, $dispatcher->getOriginalRequest());
        $this->assertEquals($request, $dispatcher->getOriginalRequest());
    }

    public function testDefaultPlugins()
    {
        $dispatcher = new \FMUP\Dispatcher();
        $this->assertSame($dispatcher, $dispatcher->defaultPlugins());
    }

    public function testDispatchWithoutPlugins()
    {
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $response = $this->getMock(\FMUP\Response::class);
        $dispatcher = $this->getMock(\FMUP\Dispatcher::class, array('setOriginalRequest', 'defaultPlugins'));
        $dispatcher->expects($this->exactly(1))->method('defaultPlugins')->willReturn($dispatcher);
        /**
         * @var $request \FMUP\Request\Cli
         * @var $response \FMUP\Response
         * @var $dispatcher \FMUP\Dispatcher
         */
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
    }

    public function testDispatchWithPlugins()
    {
        $plugin1 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin1->expects($this->exactly(2))->method('canHandle')->will($this->onConsecutiveCalls(false, true));
        $plugin1->expects($this->exactly(1))->method('handle');
        $plugin1->method('getName')->willReturn(uniqid());
        $plugin2 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin2->expects($this->exactly(2))->method('canHandle')->will($this->onConsecutiveCalls(false, false));
        $plugin2->expects($this->exactly(0))->method('handle');
        $plugin2->method('getName')->willReturn(uniqid());
        $plugin3 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin3->expects($this->exactly(2))->method('canHandle')->will($this->onConsecutiveCalls(true, false));
        $plugin3->expects($this->exactly(1))->method('handle');
        $plugin3->method('getName')->willReturn(uniqid());
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $response = $this->getMock(\FMUP\Response::class);
        $dispatcher = $this->getMock(\FMUP\Dispatcher::class, array('setOriginalRequest', 'defaultPlugins'));
        $dispatcher->expects($this->exactly(1))->method('defaultPlugins')->willReturn($dispatcher);
        /**
         * @var $request \FMUP\Request\Cli
         * @var $response \FMUP\Response
         * @var $dispatcher \FMUP\Dispatcher
         * @var $plugin1 \FMUP\Dispatcher\Plugin
         * @var $plugin2 \FMUP\Dispatcher\Plugin
         * @var $plugin3 \FMUP\Dispatcher\Plugin
         */
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin2));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin3, \FMUP\Dispatcher::WAY_APPEND));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin1, \FMUP\Dispatcher::WAY_PREPEND));
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->clear());
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
    }

    public function testDispatchPluginOrder()
    {
        $plugin1 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin1->method('getName')->willReturn(uniqid());
        $plugin1->method('canHandle')->willReturn(true);
        $plugin1->method('handle')->will($this->returnCallback(function () use ($plugin1) {
            /** @var $plugin1 \FMUP\Dispatcher\Plugin */
            $plugin1->getResponse()->setBody(str_replace('U', '_U', $plugin1->getResponse()->getBody()));
            return $plugin1;
        }));
        $plugin2 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin2->method('getName')->willReturn(uniqid());
        $plugin2->method('canHandle')->willReturn(true);
        $plugin2->method('handle')->will($this->returnCallback(function () use ($plugin2) {
            /** @var $plugin2 \FMUP\Dispatcher\Plugin */
            $plugin2->getResponse()->setBody(strtolower($plugin2->getResponse()->getBody()));
            return $plugin2;
        }));
        $plugin3 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin3->method('getName')->willReturn(uniqid());
        $plugin3->method('canHandle')->willReturn(true);
        $plugin3->method('handle')->will($this->returnCallback(function () use ($plugin3) {
            /** @var $plugin3 \FMUP\Dispatcher\Plugin */
            $plugin3->getResponse()->setBody($plugin3->getResponse()->getBody() . '_2');
            return $plugin3;
        }));
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $response = $this->getMock(\FMUP\Response::class, null);
        $dispatcher = $this->getMock(\FMUP\Dispatcher::class, array('setOriginalRequest', 'defaultPlugins'));
        $dispatcher->expects($this->exactly(1))->method('defaultPlugins')->willReturn($dispatcher);
        /**
         * @var $request \FMUP\Request\Cli
         * @var $response \FMUP\Response
         * @var $dispatcher \FMUP\Dispatcher
         * @var $plugin1 \FMUP\Dispatcher\Plugin
         * @var $plugin2 \FMUP\Dispatcher\Plugin
         * @var $plugin3 \FMUP\Dispatcher\Plugin
         */
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin2));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin3, \FMUP\Dispatcher::WAY_APPEND));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin1, \FMUP\Dispatcher::WAY_PREPEND));
        $response->setBody('testUnit');
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame('test_unit_2', $response->getBody());
    }

    public function testDispatchPluginWithReplacement()
    {
        $plugin1 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin1->method('getName')->willReturn('SAME_NAME_AS_ONE');
        $plugin1->method('canHandle')->willReturn(true);
        $plugin1->method('handle')->will($this->returnCallback(function () use ($plugin1) {
            /** @var $plugin1 \FMUP\Dispatcher\Plugin */
            $plugin1->getResponse()->setBody(str_replace('U', '_U', $plugin1->getResponse()->getBody()));
            return $plugin1;
        }));
        $plugin2 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin2->method('getName')->willReturn('SAME_NAME_AS_ONE');
        $plugin2->method('canHandle')->willReturn(true);
        $plugin2->method('handle')->will($this->returnCallback(function () use ($plugin2) {
            /** @var $plugin2 \FMUP\Dispatcher\Plugin */
            $plugin2->getResponse()->setBody(strtolower($plugin2->getResponse()->getBody()));
            return $plugin2;
        }));
        $plugin3 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin3->method('getName')->willReturn(uniqid());
        $plugin3->method('canHandle')->willReturn(true);
        $plugin3->method('handle')->will($this->returnCallback(function () use ($plugin3) {
            /** @var $plugin3 \FMUP\Dispatcher\Plugin */
            $plugin3->getResponse()->setBody($plugin3->getResponse()->getBody() . '_2');
            return $plugin3;
        }));
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $response = $this->getMock(\FMUP\Response::class, null);
        $dispatcher = $this->getMock(\FMUP\Dispatcher::class, array('setOriginalRequest', 'defaultPlugins'));
        $dispatcher->expects($this->exactly(1))->method('defaultPlugins')->willReturn($dispatcher);
        /**
         * @var $request \FMUP\Request\Cli
         * @var $response \FMUP\Response
         * @var $dispatcher \FMUP\Dispatcher
         * @var $plugin1 \FMUP\Dispatcher\Plugin
         * @var $plugin2 \FMUP\Dispatcher\Plugin
         * @var $plugin3 \FMUP\Dispatcher\Plugin
         */
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin2));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin3, \FMUP\Dispatcher::WAY_APPEND));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin1, \FMUP\Dispatcher::WAY_PREPEND));
        $response->setBody('testUnit');
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame('test_Unit_2', $response->getBody());
    }

    public function testDispatchPluginWithReplacementOnPrepend()
    {
        $plugin1 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin1->method('getName')->willReturn('SAME_NAME_AS_THREE');
        $plugin1->method('canHandle')->willReturn(true);
        $plugin1->method('handle')->will($this->returnCallback(function () use ($plugin1) {
            /** @var $plugin1 \FMUP\Dispatcher\Plugin */
            $plugin1->getResponse()->setBody(str_replace('U', '_U', $plugin1->getResponse()->getBody()));
            return $plugin1;
        }));
        $plugin2 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin2->method('getName')->willReturn('SAME_NAME_AS_ONE');
        $plugin2->method('canHandle')->willReturn(true);
        $plugin2->method('handle')->will($this->returnCallback(function () use ($plugin2) {
            /** @var $plugin2 \FMUP\Dispatcher\Plugin */
            $plugin2->getResponse()->setBody(strtolower($plugin2->getResponse()->getBody()));
            return $plugin2;
        }));
        $plugin3 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin3->method('getName')->willReturn('SAME_NAME_AS_THREE');
        $plugin3->method('canHandle')->willReturn(true);
        $plugin3->method('handle')->will($this->returnCallback(function () use ($plugin3) {
            /** @var $plugin3 \FMUP\Dispatcher\Plugin */
            $plugin3->getResponse()->setBody($plugin3->getResponse()->getBody() . '_2');
            return $plugin3;
        }));
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $response = $this->getMock(\FMUP\Response::class, null);
        $dispatcher = $this->getMock(\FMUP\Dispatcher::class, array('setOriginalRequest', 'defaultPlugins'));
        $dispatcher->expects($this->exactly(1))->method('defaultPlugins')->willReturn($dispatcher);
        /**
         * @var $request \FMUP\Request\Cli
         * @var $response \FMUP\Response
         * @var $dispatcher \FMUP\Dispatcher
         * @var $plugin1 \FMUP\Dispatcher\Plugin
         * @var $plugin2 \FMUP\Dispatcher\Plugin
         * @var $plugin3 \FMUP\Dispatcher\Plugin
         */
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin2));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin3, \FMUP\Dispatcher::WAY_APPEND));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin1, \FMUP\Dispatcher::WAY_PREPEND));
        $response->setBody('testUnit');
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame('testunit', $response->getBody());
    }

    public function testRemovePlugin()
    {
        $plugin1 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin1->method('getName')->willReturn('PLUGIN_ONE');
        $plugin1->method('canHandle')->willReturn(true);
        $plugin1->method('handle')->will($this->returnCallback(function () use ($plugin1) {
            /** @var $plugin1 \FMUP\Dispatcher\Plugin */
            $plugin1->getResponse()->setBody(str_replace('U', '_U', $plugin1->getResponse()->getBody()));
            return $plugin1;
        }));
        $plugin2 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin2->method('getName')->willReturn('PLUGIN_TWO');
        $plugin2->method('canHandle')->willReturn(true);
        $plugin2->method('handle')->will($this->returnCallback(function () use ($plugin2) {
            /** @var $plugin2 \FMUP\Dispatcher\Plugin */
            $plugin2->getResponse()->setBody(strtolower($plugin2->getResponse()->getBody()));
            return $plugin2;
        }));
        $plugin3 = $this->getMock(\FMUP\Dispatcher\Plugin::class, array('handle', 'canHandle', 'getName'));
        $plugin3->method('getName')->willReturn('PLUGIN_THREE');
        $plugin3->method('canHandle')->willReturn(true);
        $plugin3->method('handle')->will($this->returnCallback(function () use ($plugin3) {
            /** @var $plugin3 \FMUP\Dispatcher\Plugin */
            $plugin3->getResponse()->setBody($plugin3->getResponse()->getBody() . '_2');
            return $plugin3;
        }));
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $response = $this->getMock(\FMUP\Response::class, null);
        $dispatcher = $this->getMock(\FMUP\Dispatcher::class, array('setOriginalRequest', 'defaultPlugins'));
        $dispatcher->expects($this->exactly(1))->method('defaultPlugins')->willReturn($dispatcher);
        /**
         * @var $request \FMUP\Request\Cli
         * @var $response \FMUP\Response
         * @var $dispatcher \FMUP\Dispatcher
         * @var $plugin1 \FMUP\Dispatcher\Plugin
         * @var $plugin2 \FMUP\Dispatcher\Plugin
         * @var $plugin3 \FMUP\Dispatcher\Plugin
         */
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin2));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin3, \FMUP\Dispatcher::WAY_APPEND));
        $this->assertSame($dispatcher, $dispatcher->addPlugin($plugin1, \FMUP\Dispatcher::WAY_PREPEND));
        $response->setBody('testUnit');
        $this->assertSame($dispatcher, $dispatcher->removePlugin($plugin2));
        $this->assertSame($dispatcher, $dispatcher->dispatch($request, $response));
        $this->assertSame('test_Unit_2', $response->getBody());
    }
}
