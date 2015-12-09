<?php
namespace FMUP\Config\Ini\Extended;

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

use FMUP\Config\Exception;

/**
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendConfig implements \Countable, \Iterator
{
    /**
     * Whether in-memory modifications to configuration data are allowed
     *
     * @var boolean
     */
    protected $allowModifications;

    /**
     * Iteration index
     *
     * @var integer
     */
    protected $index;

    /**
     * Number of elements in configuration data
     *
     * @var integer
     */
    protected $count;

    /**
     * Contains array of configuration data
     *
     * @var array
     */
    protected $data;

    /**
     * Used when unsetting values during iteration to ensure we do not skip
     * the next element
     *
     * @var boolean
     */
    protected $skipNextIteration;

    /**
     * Contains which config file sections were loaded. This is null
     * if all sections were loaded, a string name if one section is loaded
     * and an array of string names if multiple sections were loaded.
     *
     * @var mixed
     */
    protected $loadedSection;

    /**
     * This is used to track section inheritance. The keys are names of sections that
     * extend other sections, and the values are the extended sections.
     *
     * @var array
     */
    protected $extends = array();

    /**
     * Load file error string.
     *
     * Is null if there was no error while file loading
     *
     * @var string
     */
    protected $loadFileErrorStr = null;

    /**
     * Zend_Config provides a property based interface to
     * an array. The data are read-only unless $allowModifications
     * is set to true on construction.
     *
     * Zend_Config also implements Countable and Iterator to
     * facilitate easy access to the data.
     *
     * @param  array $array
     * @param  boolean $allowModifications
     * @return void
     */
    public function __construct(array $array, $allowModifications = false)
    {
        $this->allowModifications = (boolean)$allowModifications;
        $this->loadedSection = null;
        $this->index = 0;
        $this->data = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new self($value, $this->allowModifications);
            } else {
                $this->data[$key] = $value;
            }
        }
        $this->count = count($this->data);
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $result = $default;
        if (array_key_exists($name, $this->data)) {
            $result = $this->data[$name];
        }
        return $result;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Only allow setting of a property if $allowModifications
     * was set to true on construction. Otherwise, throw an exception.
     *
     * @param  string $name
     * @param  mixed $value
     * @throws Exception
     * @return void
     */
    public function __set($name, $value)
    {
        if ($this->allowModifications) {
            if (is_array($value)) {
                $this->data[$name] = new self($value, true);
            } else {
                $this->data[$name] = $value;
            }
            $this->count = count($this->data);
        } else {
            throw new Exception('ZendConfig is read only');
        }
    }

    /**
     * Deep clone of this instance to ensure that nested Zend_Configs
     * are also cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $array = array();
        foreach ($this->data as $key => $value) {
            if ($value instanceof ZendConfig) {
                $array[$key] = clone $value;
            } else {
                $array[$key] = $value;
            }
        }
        $this->data = $array;
    }

    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $data = $this->data;
        foreach ($data as $key => $value) {
            if ($value instanceof ZendConfig) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    /**
     * Support isset() overloading on PHP 5.1
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Support unset() overloading on PHP 5.1
     *
     * @param  string $name
     * @throws Exception
     * @return void
     */
    public function __unset($name)
    {
        if ($this->allowModifications) {
            unset($this->data[$name]);
            $this->count = count($this->data);
            $this->skipNextIteration = true;
        } else {
            throw new Exception('Zend_Config is read only');
        }

    }

    /**
     * Defined by Countable interface
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        $this->skipNextIteration = false;
        return current($this->data);
    }

    /**
     * Defined by Iterator interface
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Defined by Iterator interface
     *
     */
    public function next()
    {
        if ($this->skipNextIteration) {
            $this->skipNextIteration = false;
            return;
        }
        next($this->data);
        $this->index++;
    }

    /**
     * Defined by Iterator interface
     *
     */
    public function rewind()
    {
        $this->skipNextIteration = false;
        reset($this->data);
        $this->index = 0;
    }

    /**
     * Defined by Iterator interface
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->index < $this->count;
    }

    /**
     * Returns the section name(s) loaded.
     *
     * @return mixed
     */
    public function getSectionName()
    {
        if (is_array($this->loadedSection) && count($this->loadedSection) == 1) {
            $this->loadedSection = $this->loadedSection[0];
        }
        return $this->loadedSection;
    }

    /**
     * Returns true if all sections were loaded
     *
     * @return boolean
     */
    public function areAllSectionsLoaded()
    {
        return $this->loadedSection === null;
    }


    /**
     * Merge another Zend_Config with this one. The items
     * in $merge will override the same named items in
     * the current config.
     *
     * @param ZendConfig $merge
     * @return ZendConfig
     */
    public function merge(ZendConfig $merge)
    {
        foreach ($merge as $key => $item) {
            if (array_key_exists($key, $this->data)) {
                if ($item instanceof ZendConfig && $this->$key instanceof ZendConfig) {
                    $this->$key = $this->$key->merge(new ZendConfig($item->toArray(), !$this->readOnly()));
                } else {
                    $this->$key = $item;
                }
            } else {
                if ($item instanceof ZendConfig) {
                    $this->$key = new ZendConfig($item->toArray(), !$this->readOnly());
                } else {
                    $this->$key = $item;
                }
            }
        }

        return $this;
    }

    /**
     * Prevent any more modifications being made to this instance. Useful
     * after merge() has been used to merge multiple Zend_Config objects
     * into one object which should then not be modified again.
     *
     */
    public function setReadOnly()
    {
        $this->allowModifications = false;
        foreach ($this->data as $key => $value) {
            if ($value instanceof ZendConfig) {
                $value->setReadOnly();
            }
        }
    }

    /**
     * Returns if this Zend_Config object is read only or not.
     *
     * @return boolean
     */
    public function readOnly()
    {
        return !$this->allowModifications;
    }

    /**
     * Get the current extends
     *
     * @return array
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * Set an extend for Zend_Config_Writer
     *
     * @param  string $extendingSection
     * @param  string $extendedSection
     * @return void
     */
    public function setExtend($extendingSection, $extendedSection = null)
    {
        if ($extendedSection === null && isset($this->extends[$extendingSection])) {
            unset($this->extends[$extendingSection]);
        } elseif ($extendedSection !== null) {
            $this->extends[$extendingSection] = $extendedSection;
        }
    }

    /**
     * Throws an exception if $extendingSection may not extend $extendedSection,
     * and tracks the section extension if it is valid.
     *
     * @param  string $extendingSection
     * @param  string $extendedSection
     * @throws Exception
     * @return void
     */
    protected function assertValidExtend($extendingSection, $extendedSection)
    {
        // detect circular section inheritance
        $extendedSectionCurrent = $extendedSection;
        while (array_key_exists($extendedSectionCurrent, $this->extends)) {
            if ($this->extends[$extendedSectionCurrent] == $extendingSection) {
                throw new Exception('Illegal circular inheritance detected');
            }
            $extendedSectionCurrent = $this->extends[$extendedSectionCurrent];
        }
        // remember that this section extends another section
        $this->extends[$extendingSection] = $extendedSection;
    }

    /**
     * Handle any errors from simplexml_load_file or parse_ini_file
     *
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     */
    public function loadFileErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($this->loadFileErrorStr === null) {
            $this->loadFileErrorStr = $errstr;
        } else {
            $this->loadFileErrorStr .= (PHP_EOL . $errstr);
        }
    }

    /**
     * Merge two arrays recursively, overwriting keys of the same name
     * in $firstArray with the value in $secondArray.
     *
     * @param  mixed $firstArray First array
     * @param  mixed $secondArray Second array to merge into first array
     * @return array
     */
    protected function arrayMergeRecursive($firstArray, $secondArray)
    {
        if (is_array($firstArray) && is_array($secondArray)) {
            foreach ($secondArray as $key => $value) {
                if (isset($firstArray[$key])) {
                    $firstArray[$key] = $this->arrayMergeRecursive($firstArray[$key], $value);
                } else {
                    if ($key === 0) {
                        $firstArray = array(0 => $this->arrayMergeRecursive($firstArray, $value));
                    } else {
                        $firstArray[$key] = $value;
                    }
                }
            }
        } else {
            $firstArray = $secondArray;
        }

        return $firstArray;
    }
}
