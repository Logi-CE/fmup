<?php
/**
 * Sqlite.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Db\Driver\Pdo;

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
        $logger = $this->getMock(\FMUP\Logger::class, array('log'));
        $logger->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger\Channel\System::NAME));
        $pdo = $this->getMock(\FMUP\Db\Driver\Pdo\Sqlite::class, array('getDsn', 'getPdo'));
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
        $pdoMock = $this->getMock(PdoMockDbDriverPdoSqlite::class);
        $pdo = $this->getMock(\FMUP\Db\Driver\Pdo\Sqlite::class, array('getPdo'), array(array('host' => 'localhost')));
        $pdo->expects($this->once())->method('getPdo')->willReturn($pdoMock)
            ->with($this->equalTo('sqlite:localhost' . DIRECTORY_SEPARATOR . 'pdo_sqlite.sqlite'));
        /** @var \FMUP\Db\Driver\Pdo $pdo */
        $this->assertSame($pdoMock, $pdo->getDriver());
        $this->assertSame($pdoMock, $pdo->getDriver());
    }
}
