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
        $logger = $this->getMockBuilder('\FMUP\Logger')->setMethods(array('log'))->getMock();
        $logger->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger\Channel\System::NAME));
        $pdo = $this->getMockBuilder('\FMUP\Db\Driver\Pdo\Sqlite')->setMethods(array('getDsn', 'getPdo'))->getMock();
        $pdo->method('getDsn')->willReturn('mysql:host=127.0.0.1');
        $pdo->method('getPdo')->willThrowException(new \PDOException());
        /** @var \FMUP\Logger $logger */
        /** @var \FMUP\Db\Driver\Pdo $pdo */
        $this->setExpectedException('\FMUP\Db\Exception', 'Unable to connect database');
        $pdo->setLogger($logger)->getDriver();
    }

    public function testGetDriver()
    {
        $pdoMock = $this->getMockBuilder('\Tests\Db\Driver\Pdo\PdoMockDbDriverPdoSqlite')->getMock();
        $pdo = $this->getMockBuilder('\FMUP\Db\Driver\Pdo\Sqlite')
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
