<?php
/**
 * Mysql.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Db\Driver\Pdo;

class PdoMockDbDriverPdoMysql extends \PDO
{
    public function __construct()
    {

    }
}

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDriver()
    {
        $pdo = new \FMUP\Db\Driver\Pdo\Mysql();
        $this->assertSame('mysql', $pdo->getDriver());
    }
    public function testPrepare()
    {
        $pdoMock = $this->getMockBuilder(PdoMockDbDriverPdoMysql::class)->setMethods(array('prepare'))->getMock();
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn(10)
            ->with($this->equalTo('sql'), $this->equalTo(array(\Pdo::MYSQL_ATTR_USE_BUFFERED_QUERY => true)));
        $pdo = $this->getMockBuilder(\FMUP\Db\Driver\Pdo\Mysql::class)->setMethods(array('getDriver', 'log'))->getMock();
        $pdo->method('getDriver')->willReturn($pdoMock);
        /** @var \FMUP\Db\Driver\Pdo $pdo */
        $pdo->prepare('sql');
    }
}
