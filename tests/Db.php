<?php
/**
 * Db.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

class DbFactoryMockDb extends \FMUP\Db\Factory
{
    public function __construct()
    {

    }
}

class DbTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(null)->getMock();
        $reflection = new \ReflectionProperty(\FMUP\Db::class, 'driver');
        $reflection->setAccessible(true);
        $this->assertSame(\FMUP\Db\Factory::DRIVER_PDO, $reflection->getValue($db));

        $db = $this->getMockBuilder(\FMUP\Db::class)
            ->setMethods(null)
            ->setConstructorArgs(array(array('db_driver' => 'unexisting driver')))
            ->getMock();
        $reflection = new \ReflectionProperty(\FMUP\Db::class, 'driver');
        $reflection->setAccessible(true);
        $this->assertSame('unexisting driver', $reflection->getValue($db));
    }

    public function testSetGetFactory()
    {
        $db = new \FMUP\Db;
        $factory = $db->getFactory();
        $this->assertInstanceOf(\FMUP\Db\Factory::class, $factory);
        $this->assertSame($factory, $db->getFactory());

        $factory = $this->getMockBuilder(DbFactoryMockDb::class)->getMock();
        /** @var $factory \FMUP\Db\Factory */
        $reflection = new \ReflectionProperty(\FMUP\Db\Factory::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($db->getFactory(), $factory);
        $this->assertSame($db, $db->setFactory($factory));
        $this->assertSame($factory, $db->getFactory());
    }

    public function testGetIterator()
    {
        $sql = 'SELECT * FROM UNIT_TEST';
        $statement = new \stdClass();
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('prepare')->with($this->equalTo($sql))->willReturn($statement);
        $driver->expects($this->exactly(1))
            ->method('execute')
            ->with($this->equalTo($statement), $this->equalTo(array()))
            ->willReturn($statement);
        $db = $this->getMockBuilder(\FMUP\Db::class)
            ->setMethods(array('getDriver'))
            ->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertInstanceOf(\FMUP\Db\FetchIterator::class, $db->getIterator($sql));
    }

    public function testForceReconnect()
    {
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('forceReconnect')->willReturn($driver);
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertSame($driver, $db->forceReconnect());
    }

    public function testLastInsertId()
    {
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->at(0))->method('lastInsertId')->with($this->equalTo(null))->willReturn('unitTest');
        $driver->expects($this->at(1))->method('lastInsertId')->with($this->equalTo('test'))->willReturn('unitTest');
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertSame('unitTest', $db->lastInsertId());
        $this->assertSame('unitTest', $db->lastInsertId('test'));
    }

    public function testRollback()
    {
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('rollback')->willReturn(true);
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertSame(true, $db->rollback());
    }

    public function testCommit()
    {
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('commit')->willReturn(true);
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertSame(true, $db->commit());
    }

    public function testBeginTransaction()
    {
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('beginTransaction')->willReturn(false);
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertSame(false, $db->beginTransaction());
    }

    public function testFetchRow()
    {
        $sql = 'SELECT * FROM UNIT_TEST';
        $statement = new \stdClass();
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('prepare')->with($this->equalTo($sql))->willReturn($statement);
        $driver->expects($this->exactly(1))
            ->method('execute')
            ->with($this->equalTo($statement), $this->equalTo(array()))
            ->willReturn($statement);
        $driver->expects($this->exactly(1))
            ->method('fetchRow')
            ->with($this->equalTo($statement))
            ->willReturn(array('col' => 'value'));
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertSame(array('col' => 'value'), $db->fetchRow($sql));
    }

    public function testFetchAll()
    {
        $sql = 'SELECT * FROM UNIT_TEST';
        $statement = new \stdClass();
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('prepare')->with($this->equalTo($sql))->willReturn($statement);
        $driver->expects($this->exactly(1))
            ->method('execute')
            ->with($this->equalTo($statement), $this->equalTo(array()))
            ->willReturn($statement);
        $driver->expects($this->exactly(1))
            ->method('fetchAll')
            ->with($this->equalTo($statement))
            ->willReturn(array('col' => 'value'));
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertInstanceOf(\ArrayIterator::class, $db->fetchAll($sql));
    }

    public function testQuery()
    {
        $sql = 'SELECT * FROM UNIT_TEST';
        $statement = new \stdClass();
        $driver = $this->getMockBuilder(\FMUP\Db\DbInterface::class)
            ->setMethods(
                array(
                    'beginTransaction', 'rollback', 'errorCode', 'errorInfo', 'commit', 'rawExecute', 'execute',
                    'prepare', 'lastInsertId', 'fetchRow', 'fetchAll', 'forceReconnect', 'getDriver', '__construct'
                )
            )
            ->getMock();
        $driver->expects($this->exactly(1))->method('prepare')->with($this->equalTo($sql))->willReturn($statement);
        $driver->expects($this->exactly(1))
            ->method('execute')
            ->with($this->equalTo($statement), $this->equalTo(array()))
            ->willReturn($statement);
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('getDriver'))->getMock();
        $db->method('getDriver')->willReturn($driver);
        /** @var $db \FMUP\Db */
        $this->assertSame($statement, $db->query($sql));
    }

    public function testGetDriver()
    {
        $db = $this->getMockBuilder(\FMUP\Db::class)->setMethods(array('hasLogger', 'getLogger'))->getMock();
        $db->expects($this->exactly(1))->method('hasLogger')->willReturn(true);
        $db->expects($this->exactly(1))->method('getLogger')->willReturn(
            $this->getMockBuilder(\FMUP\Logger::class)->setMethods(null)->getMock()
        );
        /**
         * @var $db \FMUP\Db
         * @var $factory \FMUP\Db\Factory
         */
        $retrievedDriver = $db->getDriver();
        $this->assertInstanceOf(\FMUP\Db\DbInterface::class, $retrievedDriver);
        $this->assertSame($retrievedDriver, $db->getDriver());
    }
}
