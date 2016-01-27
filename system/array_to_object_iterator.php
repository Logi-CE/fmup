<?php

/**
 * Transforme un tableau de valeur en objet
 *
 * @author csanz@castelis.com
 * @author jmoulin@castelis.com
 */
class ArrayToObjectIterator extends \IteratorIterator implements \Countable, \ArrayAccess, \SeekableIterator
{
    private $className;
    private $current;
    private $row = 0;

    public function __construct(\Iterator $fIterator, $className)
    {
        parent::__construct($fIterator);
        $this->className = $className;
    }

    private function defineCurrentToObject()
    {
        $this->current = $this->getInnerIterator()->valid()
            ? \Model::objectsFromArray($this->getInnerIterator()->current(), $this->className)
            : null;
    }

    public function rewind()
    {
        $this->row = 0;
        $this->getInnerIterator()->rewind();
        $this->defineCurrentToObject();
    }

    public function current()
    {
        return $this->current;
    }

    public function valid()
    {
        return $this->getInnerIterator()->valid();
    }

    public function next()
    {
        $this->getInnerIterator()->next();
        if ($this->getInnerIterator()->valid()) {
            $this->defineCurrentToObject();
        } else {
            $this->current = null;
        }
        $this->row++;
    }

    public function count()
    {
        return iterator_count($this->getInnerIterator());
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
        return isset($this->getInnerIterator()[$offset]);
    }

    /**
     * @param mixed $offset
     * @return array|object
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset)
            ? \Model::objectsFromArray($this->getInnerIterator()[$offset], $this->className)
            : null;
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
     * @param int $position
     */
    public function seek($position)
    {
        $this->row = (int)$position;
        $iterator = $this->getInnerIterator();
        if ($iterator instanceof \SeekableIterator) {
            $iterator->seek($position);
        }
    }
}
