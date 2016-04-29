<?php
/**
 * FetchIterator.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Db;


class FetchIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAndSetGetStatementAndSetGetDbInterface()
    {
        $dbInterface = $this->getMock(
            \FMUP\Db\DbInterface::class,
            array(
                '__construct',
                'beginTransaction',
                'rollback',
                'errorCode',
                'errorInfo',
                'commit',
                'rawExecute',
                'execute',
                'prepare',
                'lastInsertId',
                'fetchRow',
                'fetchAll',
                'forceReconnect',
                'getDriver'
            )
        );
        $dbInterface2 = $this->getMock(
            \FMUP\Db\DbInterface::class,
            array(
                '__construct',
                'beginTransaction',
                'rollback',
                'errorCode',
                'errorInfo',
                'commit',
                'rawExecute',
                'execute',
                'prepare',
                'lastInsertId',
                'fetchRow',
                'fetchAll',
                'forceReconnect',
                'getDriver'
            )
        );
        /** @var \FMUP\Db\DbInterface $dbInterface */
        /** @var \FMUP\Db\DbInterface $dbInterface2 */
        $statement = new \stdClass();
        $fetchIterator = new \FMUP\Db\FetchIterator($statement, $dbInterface);
        $this->assertInstanceOf(\Iterator::class, $fetchIterator);
        $this->assertSame($statement, $fetchIterator->getStatement());
        $this->assertSame($dbInterface, $fetchIterator->getDbInterface());
        $statement2 = new \stdClass();
        $this->assertSame($fetchIterator, $fetchIterator->setStatement($statement2));
        $this->assertSame($statement2, $fetchIterator->getStatement());
        $this->assertNotSame($statement, $fetchIterator->getStatement());

        $this->assertSame($fetchIterator, $fetchIterator->setDbInterface($dbInterface2));
        $this->assertSame($dbInterface2, $fetchIterator->getDbInterface());
        $this->assertNotSame($dbInterface, $fetchIterator->getDbInterface());
    }

    public function testIterations()
    {
        $statement = $this->getMock(\PDOStatement::class, array('execute'));
        $statement->expects($this->once())->method('execute');
        $dbInterface = $this->getMock(
            \FMUP\Db\DbInterface::class,
            array(
                '__construct',
                'beginTransaction',
                'rollback',
                'errorCode',
                'errorInfo',
                'commit',
                'rawExecute',
                'execute',
                'prepare',
                'lastInsertId',
                'fetchRow',
                'fetchAll',
                'forceReconnect',
                'getDriver'
            )
        );
        $dbInterface->method('fetchRow')->willReturnOnConsecutiveCalls(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $iterator = $this->getMock(\FMUP\Db\FetchIterator::class, null, array($statement, $dbInterface));
        $total = 0;
        $expectedTotal = 10;
        foreach ($iterator as $key => $value) {
            $total++;
            $this->assertSame($key + 1, $value);
        }
        $this->assertSame($expectedTotal, $total);
    }

    public function testSeek()
    {
        $statement = $this->getMock(\PDOStatement::class, array('execute'));
        $statement->expects($this->once())->method('execute');
        $dbInterface = $this->getMock(
            \FMUP\Db\DbInterface::class,
            array(
                '__construct',
                'beginTransaction',
                'rollback',
                'errorCode',
                'errorInfo',
                'commit',
                'rawExecute',
                'execute',
                'prepare',
                'lastInsertId',
                'fetchRow',
                'fetchAll',
                'forceReconnect',
                'getDriver'
            )
        );
        $dbInterface->expects($this->exactly(3))->method('fetchRow')->willReturnOnConsecutiveCalls(1, false, 2);
        $iterator = $this->getMock(\FMUP\Db\FetchIterator::class, null, array($statement, $dbInterface));
        /** @var $iterator \FMUP\Db\FetchIterator */
        $iterator->seek(10);
        $this->assertSame(10, $iterator->key());
        $this->assertSame(1, $iterator->current());
        $iterator->seek(2540);
        $this->assertSame(2540, $iterator->key());
        $this->assertSame(2, $iterator->current());
    }

    public function testOffsets()
    {
        $statement = $this->getMock(\PDOStatement::class, array('execute'));
        $dbInterface = $this->getMock(
            \FMUP\Db\DbInterface::class,
            array(
                '__construct',
                'beginTransaction',
                'rollback',
                'errorCode',
                'errorInfo',
                'commit',
                'rawExecute',
                'execute',
                'prepare',
                'lastInsertId',
                'fetchRow',
                'fetchAll',
                'forceReconnect',
                'getDriver'
            )
        );
        $dbInterface->method('fetchRow')->willReturnOnConsecutiveCalls(1, false, false, 2, false, 3, false, false);
        $iterator = $this->getMock(\FMUP\Db\FetchIterator::class, null, array($statement, $dbInterface));
        /** @var $iterator \FMUP\Db\FetchIterator */
        $this->assertTrue(isset($iterator[10]));
        $this->assertFalse(isset($iterator[1]));
        $this->assertSame(2, $iterator[1]);
        $this->assertSame(3, $iterator[2]);
        $this->assertNull($iterator[3]);
    }

    public function testOffsetSet()
    {
        $statement = $this->getMock(\PDOStatement::class, array('execute'));
        $dbInterface = $this->getMock(
            \FMUP\Db\DbInterface::class,
            array(
                '__construct',
                'beginTransaction',
                'rollback',
                'errorCode',
                'errorInfo',
                'commit',
                'rawExecute',
                'execute',
                'prepare',
                'lastInsertId',
                'fetchRow',
                'fetchAll',
                'forceReconnect',
                'getDriver'
            )
        );
        $iterator = $this->getMock(\FMUP\Db\FetchIterator::class, null, array($statement, $dbInterface));
        /** @var $iterator \FMUP\Db\FetchIterator */
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessage('Unable to set index on iterator');
        $iterator[10] = 10;
    }

    public function testOffsetUnset()
    {
        $statement = $this->getMock(\PDOStatement::class, array('execute'));
        $dbInterface = $this->getMock(
            \FMUP\Db\DbInterface::class,
            array(
                '__construct',
                'beginTransaction',
                'rollback',
                'errorCode',
                'errorInfo',
                'commit',
                'rawExecute',
                'execute',
                'prepare',
                'lastInsertId',
                'fetchRow',
                'fetchAll',
                'forceReconnect',
                'getDriver'
            )
        );
        $iterator = $this->getMock(\FMUP\Db\FetchIterator::class, null, array($statement, $dbInterface));
        /** @var $iterator \FMUP\Db\FetchIterator */
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessage('Unable to unset index on iterator');
        unset($iterator[10]);
    }
}
