<?php
namespace FMUP\Db;

/**
 * Class FetchIterator
 * @package FMUP\Db
 * @author csanz@castelis.com
 * @author jmoulin@castelis.com
 */
class FetchIterator implements \Iterator, \ArrayAccess, \SeekableIterator
{
    /**
     * @var mixed
     */
    private $statement;

    /**
     * @var DbInterface
     */
    private $dbInterface;

    /**
     * @var array
     */
    private $current;

    /**
     * @var int
     */
    private $row = 0;

    /**
     * @param $statement
     * @param DbInterface $dbInterface
     */
    public function __construct($statement, DbInterface $dbInterface)
    {
        $this->setStatement($statement)->setDbInterface($dbInterface);
    }

    /**
     * @param mixed $statement
     * @return $this
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;
        return $this;
    }

    /**
     * @return mixed|\PDOStatement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @return DbInterface
     */
    public function getDbInterface()
    {
        return $this->dbInterface;
    }

    /**
     * @param DbInterface $dbInterface
     * @return $this
     */
    public function setDbInterface(DbInterface $dbInterface)
    {
        $this->dbInterface = $dbInterface;
        return $this;
    }

    /**
     * Return format depends on Fetch Mode
     * @return array|object
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->current = $this->getDbInterface()->fetchRow($this->getStatement());
        $this->row++;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return (bool)($this->current());
    }

    public function rewind()
    {
        $this->row = 0;
        $this->getStatement()->execute();
        $this->current = $this->getDbInterface()->fetchRow($this->getStatement(), DbInterface::CURSOR_FIRST);
    }

    /**
     * @return int
     */
    public function key()
    {
        return (int)$this->row;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        $this->seek($offset);
        return $this->valid();
    }

    /**
     * @param mixed $offset
     * @return array|object
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->current() : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception('Unable to set index on iterator');
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception('Unable to unset index on iterator');
    }

    /**
     * @param int $offset
     */
    public function seek($offset)
    {
        $this->row = (int)$offset;
        $this->current = $this->getDbInterface()->fetchRow($this->getStatement(), DbInterface::CURSOR_FIRST, $this->row);
        if ($this->current === false) {
            $this->getStatement()->execute();
            $this->current = $this->getDbInterface()->fetchRow($this->getStatement(), DbInterface::CURSOR_FIRST, $this->row);
        }
    }
}
