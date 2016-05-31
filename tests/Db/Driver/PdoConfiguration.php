<?php
/**
 * PdoConfigurationConfiguration.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Db\Driver;

use FMUP\Db\Driver\PdoConfiguration;
use FMUP\Db\Exception;

class PdoMockDbDriverPdoConfiguration extends \PDO
{
    public function __construct()
    {

    }
}

class PdoConfigurationMockDbDriverPdoConfiguration extends \FMUP\Db\Driver\PdoConfiguration
{
    public function beginTransaction()
    {
        // TODO: Implement beginTransaction() method.
    }

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    public function errorCode()
    {
        // TODO: Implement errorCode() method.
    }

    public function errorInfo()
    {
        // TODO: Implement errorInfo() method.
    }

    protected function defaultConfiguration(\Pdo $instance)
    {
        // TODO: Implement defaultConfiguration() method.
    }

    public function execute($statement, $values = array())
    {
        // TODO: Implement execute() method.
    }

    public function fetchAll($statement)
    {
        // TODO: Implement fetchAll() method.
    }

    public function lastInsertId($name = null)
    {
        // TODO: Implement lastInsertId() method.
    }

    public function prepare($sql)
    {
        // TODO: Implement prepare() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
}

class PdoConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testLoggerName()
    {
        $logger = $this->getMock(\FMUP\Logger::class, array('log'));
        $logger->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger\Channel\System::NAME));
        /** @var \FMUP\Logger $logger */
        $pdo = new PdoConfigurationMockDbDriverPdoConfiguration;
        /** @var \FMUP\Db\Driver\PdoConfiguration $pdo */
        $pdo->setLogger($logger)->log(\FMUP\Logger::ALERT, 'test');
    }

    public function testGetDriverWhenFail()
    {
        $logger = $this->getMock(\FMUP\Logger::class, array('log'));
        $logger->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger\Channel\System::NAME));
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('getDsn', 'getLogin', 'getPassword'));
        $pdo->method('getDsn')->willReturn('mysql:host=127.0.0.1');
        /** @var \FMUP\Logger $logger */
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessage('Unable to connect database');
        $pdo->setLogger($logger)->getDriver();
    }

    public function testGetDriver()
    {
        $pdoMock = $this->getMock(PdoMockDbDriverPdoConfiguration::class);
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('getPdo'));
        $pdo->expects($this->once())->method('getPdo')->willReturn($pdoMock);
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->assertSame($pdoMock, $pdo->getDriver());
        $this->assertSame($pdoMock, $pdo->getDriver());
    }

    public function testForceReconnect()
    {
        $pdoMock = $this->getMock(PdoMockDbDriverPdoConfiguration::class);
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('getPdo'));
        $pdo->expects($this->exactly(2))->method('getPdo')->willReturn($pdoMock);
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->assertSame($pdoMock, $pdo->getDriver());
        $this->assertSame($pdoMock, $pdo->getDriver());
        $this->assertSame($pdo, $pdo->forceReconnect());
        $this->assertSame($pdoMock, $pdo->getDriver());
        $this->assertSame($pdoMock, $pdo->getDriver());
    }

    public function testGetDriverWithDatabase()
    {
        $pdoMock = $this->getMock(PdoMockDbDriverPdoConfiguration::class);
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('getPdo'), array(array('database' => 'unitTest')));
        $pdo->expects($this->once())->method('getPdo')->willReturn($pdoMock)
            ->with($this->equalTo('mysql:host=localhost;dbname=unitTest'));
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->assertSame($pdoMock, $pdo->getDriver());
    }

    public function testRawExecuteFailRandom()
    {
        $pdoMock = $this->getMock(PdoMockDbDriverPdoConfiguration::class, array('prepare'));
        $pdoMock->expects($this->once())->method('prepare')->willThrowException(new \PDOException('random message'));
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('getDriver', 'log'));
        $pdo->expects($this->once())->method('getDriver')->willReturn($pdoMock);
        $pdo->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger::ERROR), $this->equalTo('random message'));
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessage('random message');
        $pdo->rawExecute('sql');
    }

    public function testRawExecute()
    {
        $statement = $this->getMock(\PDOStatement::class, array('execute'));
        $statement->expects($this->once())->method('execute')->willReturn(true);
        $pdoMock = $this->getMock(PdoMockDbDriverPdoConfiguration::class, array('prepare'));
        $pdoMock->expects($this->once())->method('prepare')->willReturn($statement);
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('getDriver', 'log'));
        $pdo->method('getDriver')->willReturn($pdoMock);
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->assertTrue($pdo->rawExecute('sql'));
    }


    public function testFetchRowFailNotStatement()
    {
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('log'));
        $pdo->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger::ERROR), $this->equalTo('Statement not in right format'));
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessage('Statement not in right format');
        $pdo->fetchRow('sql');
    }

    public function testFetchRowFailRandom()
    {
        $statement = $this->getMock(\PDOStatement::class, array('fetch'));
        $statement->expects($this->once())->method('fetch')->willThrowException(new \PDOException('random message'));
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('log'));
        $pdo->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger::ERROR), $this->equalTo('random message'));
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessage('random message');
        $pdo->fetchRow($statement, array('test' => 'test'));
    }

    public function testFetchRow()
    {
        $statement = $this->getMock(\PDOStatement::class, array('fetch'));
        $statement->expects($this->once())->method('fetch')
            ->willReturn(array())
            ->with(
                $this->equalTo(1),
                $this->equalTo(PdoConfigurationMockDbDriverPdoConfiguration::CURSOR_NEXT),
                $this->equalTo(3)
            );
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('getFetchMode'));
        $pdo->expects($this->once())->method('getFetchMode')->willReturn(1);
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->assertTrue(
            is_array(
                $pdo->fetchRow($statement, PdoConfigurationMockDbDriverPdoConfiguration::CURSOR_NEXT, 3)
            )
        );
    }

    public function testSetGetFetchMode()
    {
        $pdo = $this->getMock(PdoConfigurationMockDbDriverPdoConfiguration::class, array('log'));
        $pdo->expects($this->once())->method('log')->with($this->equalTo(\FMUP\Logger::DEBUG), $this->equalTo('Fetch Mode changed'));
        /** @var PdoConfigurationMockDbDriverPdoConfiguration $pdo */
        $this->assertSame(\PDO::FETCH_ASSOC, $pdo->getFetchMode());
        $this->assertSame($pdo, $pdo->setFetchMode(\PDO::FETCH_BOTH));
        $this->assertSame(\PDO::FETCH_BOTH, $pdo->getFetchMode());
    }
}
