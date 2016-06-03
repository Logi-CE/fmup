<?php
/**
 * Odbc.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Db\Driver\Pdo;

class PdoMockDbDriverPdoOdbc extends \PDO
{
    public function __construct()
    {

    }
}

class OdbcTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfigurationAndGetDsn()
    {
        $pdoMock = $this->getMockBuilder(PdoMockDbDriverPdoOdbc::class)->setMethods(array('setAttribute'))->getMock();
        $pdoMock->method('setAttribute');
        $pdo = $this->getMockBuilder(\FMUP\Db\Driver\Pdo\Odbc::class)
            ->setMethods(array('getPdo', 'log', 'getDatabase'))
            ->getMock();
        $pdo->expects($this->once())->method('getDatabase')->willReturn('unitTest');
        $pdo->expects($this->once())->method('getPdo')->willReturn($pdoMock)->with($this->equalTo('odbc:Driver={mysql};Server={localhost};Database={unitTest}'));
        /** @var \FMUP\Db\Driver\Pdo $pdo */
        $this->assertSame($pdoMock, $pdo->getDriver());
    }
}
