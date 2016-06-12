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
        $dbInterface = $this->getMockBuilder('\FMUP\Db\DbInterface')
            ->setMethods(
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
            )
            ->getMock();
        $dbInterface2 = $this->getMockBuilder('\FMUP\Db\DbInterface')
            ->setMethods(
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
            )
            ->getMock();
        /** @var \FMUP\Db\DbInterface $dbInterface */
        /** @var \FMUP\Db\DbInterface $dbInterface2 */
        $statement = new \stdClass();
        $fetchIterator = new \FMUP\Db\FetchIterator($statement, $dbInterface);
        $this->assertInstanceOf('\Iterator', $fetchIterator);
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
        $statement = $this->getMockBuilder('\PDOStatement')->setMethods(array('execute'))->getMock();
        $statement->expects($this->once())->method('execute');
        $dbInterface = $this->getMockBuilder('\FMUP\Db\DbInterface')
            ->setMethods(
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
            )
            ->getMock();
        $dbInterface->method('fetchRow')->willReturnOnConsecutiveCalls(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $iterator = $this->getMockBuilder('\FMUP\Db\FetchIterator')
            ->setMethods(null)
            ->setConstructorArgs(array($statement, $dbInterface))
            ->getMock();
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
        $statement = $this->getMockBuilder('\PDOStatement')->setMethods(array('execute'))->getMock();
        $statement->expects($this->once())->method('execute');
        $dbInterface = $this->getMockBuilder('\FMUP\Db\DbInterface')
            ->setMethods(
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
            )
            ->getMock();
        $dbInterface->expects($this->exactly(3))->method('fetchRow')->willReturnOnConsecutiveCalls(1, false, 2);
        $iterator = $this->getMockBuilder('\FMUP\Db\FetchIterator')
            ->setMethods(null)
            ->setConstructorArgs(array($statement, $dbInterface))
            ->getMock();
        $reflector = new \ReflectionMethod('\FMUP\Db\FetchIterator', 'seek');
        $reflector->setAccessible(true);
        /** @var $iterator \FMUP\Db\FetchIterator */
        $reflector->invoke($iterator, 10);
        $this->assertSame(10, $iterator->key());
        $this->assertSame(1, $iterator->current());
        $reflector->invoke($iterator, 2540);
        $this->assertSame(2540, $iterator->key());
        $this->assertSame(2, $iterator->current());
    }

    public function testOffsets()
    {
        $statement = $this->getMockBuilder('\PDOStatement')->setMethods(array('execute'))->getMock();
        $dbInterface = $this->getMockBuilder('\FMUP\Db\DbInterface')
            ->setMethods(
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
            )
            ->getMock();
        $dbInterface->method('fetchRow')->willReturnOnConsecutiveCalls(1, false, false, 2, false, 3, false, false);
        $iterator = $this->getMockBuilder('\FMUP\Db\FetchIterator')
            ->setMethods(null)
            ->setConstructorArgs(array($statement, $dbInterface))
            ->getMock();
        /** @var $iterator \FMUP\Db\FetchIterator */
        $this->assertTrue(isset($iterator[10]));
        $this->assertFalse(isset($iterator[1]));
        $this->assertSame(2, $iterator[1]);
        $this->assertSame(3, $iterator[2]);
        $this->assertNull($iterator[3]);
    }

    public function testOffsetSet()
    {
        $statement = $this->getMockBuilder('\PDOStatement')->setMethods(array('execute'))->getMock();
        $dbInterface = $this->getMockBuilder('\FMUP\Db\DbInterface')
            ->setMethods(
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
            )
            ->getMock();
        $iterator = $this->getMockBuilder('\FMUP\Db\FetchIterator')
            ->setMethods(null)
            ->setConstructorArgs(array($statement, $dbInterface))
            ->getMock();
        /** @var $iterator \FMUP\Db\FetchIterator */
        $this->setExpectedException('\FMUP\Db\Exception', 'Unable to set offset 10 to value 12 on iterator');
        $iterator[10] = 12;
    }

    public function testOffsetUnset()
    {
        $statement = $this->getMockBuilder('\PDOStatement')->setMethods(array('execute'))->getMock();
        $dbInterface = $this->getMockBuilder('\FMUP\Db\DbInterface')
            ->setMethods(
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
            )
            ->getMock();
        $iterator = $this->getMockBuilder('\FMUP\Db\FetchIterator')
            ->setMethods(null)
            ->setConstructorArgs(array($statement, $dbInterface))
            ->getMock();
        /** @var $iterator \FMUP\Db\FetchIterator */
        $this->setExpectedException('\FMUP\Db\Exception', 'Unable to unset offset 10 on iterator');
        unset($iterator[10]);
    }
}
