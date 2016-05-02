<?php
/**
 * SqlSrv.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Db\Driver\Pdo;

class PdoMockDbDriverPdoSqlSrv extends \PDO
{
    public function __construct()
    {

    }
}

class SqlSrvTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOptionsAndGetDsn()
    {
        $pdoMock = $this->getMock(PdoMockDbDriverPdoSqlSrv::class, array('setAttribute'));
        $pdo = $this->getMock(\FMUP\Db\Driver\Pdo\SqlSrv::class, array('getPdo', 'log', 'getDatabase', 'defaultConfiguration'));
        $pdo->expects($this->once())->method('getDatabase')->willReturn('unitTest');
        $pdo->expects($this->once())->method('getPdo')->willReturn($pdoMock)->with(
            $this->equalTo('sqlsrv:Server={localhost};Database={unitTest};'),
            $this->equalTo(''),
            $this->equalTo('')
        );
        /** @var \FMUP\Db\Driver\Pdo $pdo */
        $this->assertSame($pdoMock, $pdo->getDriver());
    }
}
