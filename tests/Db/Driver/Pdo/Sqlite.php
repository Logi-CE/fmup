<?php
/**
 * Sqlite.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Db\Driver\Pdo;

class PdoMockDbDriverPdoSqlite extends \PDO
{
    public function __construct()
    {

    }
}

class SqliteTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDriverWhenFail()
    {
        $logger = $this->getMockBuilder(\FMUP\Logger::class)->setMethods(array('log'))->getMock();
        $logger->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger\Channel\System::NAME));
        $pdo = $this->getMockBuilder(\FMUP\Db\Driver\Pdo\Sqlite::class)->setMethods(array('getDsn', 'getPdo'))->getMock();
        $pdo->method('getDsn')->willReturn('mysql:host=127.0.0.1');
        $pdo->method('getPdo')->willThrowException(new \PDOException());
        /** @var \FMUP\Logger $logger */
        /** @var \FMUP\Db\Driver\Pdo $pdo */
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessage('Unable to connect database');
        $pdo->setLogger($logger)->getDriver();
    }

    public function testGetDriver()
    {
        $pdoMock = $this->getMockBuilder(PdoMockDbDriverPdoSqlite::class)->getMock();
        $pdo = $this->getMockBuilder(\FMUP\Db\Driver\Pdo\Sqlite::class)
            ->setMethods(array('getPdo'))
            ->setConstructorArgs(array(array('host' => 'localhost')))
            ->getMock();
        $pdo->expects($this->once())->method('getPdo')->willReturn($pdoMock)
            ->with($this->equalTo('sqlite:localhost' . DIRECTORY_SEPARATOR . 'pdo_sqlite.sqlite'));
        /** @var \FMUP\Db\Driver\Pdo $pdo */
        $this->assertSame($pdoMock, $pdo->getDriver());
        $this->assertSame($pdoMock, $pdo->getDriver());
    }
}
