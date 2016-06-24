<?php
/**
 * View.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\FlashMessenger;


class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPath()
    {
        $this->assertTrue(is_dir(\FMUP\FlashMessenger\View::getPath()));
    }
}
