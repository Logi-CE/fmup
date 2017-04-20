<?php
namespace FMUP\Db;

/**
 * Class FetchIterator
 * @package FMUP\Db
 * @author csanz@castelis.com
 * @author jmoulin@castelis.com
 */
class FetchIterator implements \Iterator, \ArrayAccess
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
     * @var array
     */
    private $params = array();

    /**
     * @var int
     */
    private $row = 0;

    /**
     * @param mixed $statement
     * @param DbInterface $dbInterface
     * @param array $params
     */
    public function __construct($statement, DbInterface $dbInterface, array $params = array())
    {
        $this->setStatement($statement)->setDbInterface($dbInterface)->setParams($params);
    }

    /**
     * Define params for the prepared statement
     * @param array $params
     * @return $this
     */
    public function setParams(array $params = array())
    {
        $this->params = $params;
        return $this;
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
        $this->getDbInterface()->execute($this->getStatement(), $this->params);
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
        throw new Exception("Unable to set offset $offset to value $value on iterator");
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception("Unable to unset offset $offset on iterator");
    }

    /**
     * @param int $offset
     */
    protected function seek($offset)
    {
        $this->row = (int)$offset;
        $this->current = $this->getDbInterface()->fetchRow(
            $this->getStatement(),
            DbInterface::CURSOR_FIRST,
            $this->row
        );
        if ($this->current === false) {
            $this->getStatement()->execute();
            $this->current = $this->getDbInterface()->fetchRow(
                $this->getStatement(),
                DbInterface::CURSOR_FIRST,
                $this->row
            );
        }
    }
}
