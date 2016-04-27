<?php
/**
 * NotFound.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Exception\Status;


class NotFoundTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStatus()
    {
        $this->assertSame(
            \FMUP\Response\Header\Status::VALUE_NOT_FOUND,
            (new \FMUP\Exception\Status\NotFound())->getStatus()
        );
    }
}
