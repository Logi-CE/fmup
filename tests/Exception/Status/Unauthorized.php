<?php
/**
 * Unauthorized.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Exception\Status;


class UnauthorizedTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStatus()
    {
        $this->assertSame(
            \FMUP\Response\Header\Status::VALUE_UNAUTHORIZED,
            (new \FMUP\Exception\Status\Unauthorized())->getStatus()
        );
    }
}
