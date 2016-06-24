<?php
/**
 * Forbidden.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Exception\Status;


class ForbiddenTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStatus()
    {
        $this->assertSame(
            \FMUP\Response\Header\Status::VALUE_FORBIDDEN,
            (new \FMUP\Exception\Status\Forbidden())->getStatus()
        );
    }
}
