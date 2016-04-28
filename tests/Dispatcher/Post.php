<?php
/**
 * Post.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Dispatcher;


class PostTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultPlugins()
    {
        $post = new \FMUP\Dispatcher\Post();
        $this->assertInstanceOf(\FMUP\Dispatcher::class, $post);
        $this->assertSame($post, $post->defaultPlugins());
    }
}
