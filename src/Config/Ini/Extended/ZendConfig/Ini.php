<?php
namespace FMUP\Config\Ini\Extended\ZendConfig;

use FMUP\Config\Exception;
use FMUP\Config\Ini\Extended\ZendConfig;

/**
 * @see ZendConfig
 */

/**
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @codeCoverageIgnore since its an open source component
 * @SuppressWarnings(PHPMD)
 */
class Ini extends ZendConfig
{
    /**
     * StringHandle that separates nesting levels of configuration data identifiers
     *
     * @var string
     */
    protected $nestSeparator = '.';

    /**
     * StringHandle that separates the parent section name
     *
     * @var string
     */
    protected $sectionSeparator = ':';

    /**
     * Whether to skip extends or not
     *
     * @var boolean
     */
    protected $skipExtends = false;

    /**
     * Loads the section $section from the config file $filename for
     * access facilitated by nested object properties.
     *
     * If the section name contains a ":" then the section name to the right
     * is loaded and included into the properties. Note that the keys in
     * this $section will override any keys of the same
     * name in the sections that have been included via ":".
     *
     * If the $section is null, then all sections in the ini file are loaded.
     *
     * If any key includes a ".", then this will act as a separator to
     * create a sub-property.
     *
     * example ini file:
     *      [all]
     *      db.connection = database
     *      hostname = live
     *
     *      [staging : all]
     *      hostname = staging
     *
     * after calling $data = new Zend_Config_Ini($file, 'staging'); then
     *      $data->hostname === "staging"
     *      $data->db->connection === "database"
     *
     * The $options parameter may be provided as either a boolean or an array.
     * If provided as a boolean, this sets the $allowModifications option of
     * Zend_Config. If provided as an array, there are three configuration
     * directives that may be set. For example:
     *
     * $options = array(
     *     'allowModifications' => false,
     *     'nestSeparator'      => ':',
     *     'skipExtends'        => false,
     *      );
     *
     * @param  string $filename
     * @param  mixed $section
     * @param  boolean|array $options
     * @throws Exception
     */
    public function __construct($filename, $section = null, $options = false)
    {
        if (empty($filename)) {
            throw new Exception('Filename is not set');
        }

        $allowModifications = false;
        if (is_bool($options)) {
            $allowModifications = $options;
        } elseif (is_array($options)) {
            if (isset($options['allowModifications'])) {
                $allowModifications = (bool)$options['allowModifications'];
            }
            if (isset($options['nestSeparator'])) {
                $this->nestSeparator = (string)$options['nestSeparator'];
            }
            if (isset($options['skipExtends'])) {
                $this->skipExtends = (bool)$options['skipExtends'];
            }
        }

        $iniArray = $this->loadIniFile($filename);

        if (null === $section) {
            // Load entire file
            $dataArray = array();
            foreach ($iniArray as $sectionName => $sectionData) {
                if (!is_array($sectionData)) {
                    $dataArray = $this->arrayMergeRecursive(
                        $dataArray,
                        $this->processKey(array(), $sectionName, $sectionData)
                    );
                } else {
                    $dataArray[$sectionName] = $this->processSection($iniArray, $sectionName);
                }
            }
            parent::__construct($dataArray, $allowModifications);
        } else {
            // Load one or more sections
            if (!is_array($section)) {
                $section = array($section);
            }
            $dataArray = array();
            foreach ($section as $sectionName) {
                if (!isset($iniArray[$sectionName])) {
                    throw new Exception("Section '$sectionName' cannot be found in $filename");
                }
                $dataArray = $this->arrayMergeRecursive($this->processSection($iniArray, $sectionName), $dataArray);
            }
            parent::__construct($dataArray, $allowModifications);
        }

        $this->loadedSection = $section;
    }

    /**
     * Load the INI file from disk using parse_ini_file(). Use a private error
     * handler to convert any loading errors into a Zend_Config_Exception
     *
     * @param string $filename
     * @throws Exception
     * @return array
     */
    protected function parseIniFile($filename)
    {
        set_error_handler(array($this, 'loadFileErrorHandler'));
        $iniArray = parse_ini_file($filename, true); // Warnings and errors are suppressed
        restore_error_handler();

        // Check if there was a error while loading file
        if ($this->loadFileErrorStr !== null) {
            throw new Exception($this->loadFileErrorStr);
        }

        return $iniArray;
    }

    /**
     * Load the ini file and preprocess the section separator (':' in the
     * section name (that is used for section extension) so that the resultant
     * array has the correct section names and the extension information is
     * stored in a sub-key called ';extends'. We use ';extends' as this can
     * never be a valid key name in an INI file that has been loaded using
     * parse_ini_file().
     *
     * @param string $filename
     * @throws Exception
     * @return array
     */
    protected function loadIniFile($filename)
    {
        $loaded = $this->parseIniFile($filename);
        $iniArray = array();
        foreach ($loaded as $key => $data) {
            $pieces = explode($this->sectionSeparator, $key);
            $thisSection = trim($pieces[0]);
            switch (count($pieces)) {
                case 1:
                    $iniArray[$thisSection] = $data;
                    break;

                case 2:
                    $extendedSection = trim($pieces[1]);
                    $iniArray[$thisSection] = array_merge(array(';extends' => $extendedSection), $data);
                    break;

                default:
                    throw new Exception("Section '$thisSection' may not extend multiple sections in $filename");
            }
        }

        return $iniArray;
    }

    /**
     * Process each element in the section and handle the ";extends" inheritance
     * key. Passes control to processKey() to handle the nest separator
     * sub-property syntax that may be used within the key name.
     *
     * @param  array $iniArray
     * @param  string $section
     * @param  array $config
     * @throws Exception
     * @return array
     */
    protected function processSection($iniArray, $section, $config = array())
    {
        $thisSection = $iniArray[$section];

        foreach ($thisSection as $key => $value) {
            if (strtolower($key) == ';extends') {
                if (isset($iniArray[$value])) {
                    $this->assertValidExtend($section, $value);

                    if (!$this->skipExtends) {
                        $config = $this->processSection($iniArray, $value, $config);
                    }
                } else {
                    throw new Exception("Parent section '$section' cannot be found");
                }
            } else {
                $config = $this->processKey($config, $key, $value);
            }
        }
        return $config;
    }

    /**
     * Assign the key's value to the property list. Handles the
     * nest separator for sub-properties.
     *
     * @param  array $config
     * @param  string $key
     * @param  string $value
     * @throws Exception
     * @return array
     */
    protected function processKey($config, $key, $value)
    {
        if (strpos($key, $this->nestSeparator) !== false) {
            $pieces = explode($this->nestSeparator, $key, 2);
            if (strlen($pieces[0]) && strlen($pieces[1])) {
                if (!isset($config[$pieces[0]])) {
                    if ($pieces[0] === '0' && !empty($config)) {
                        // convert the current values in $config into an array
                        $config = array($pieces[0] => $config);
                    } else {
                        $config[$pieces[0]] = array();
                    }
                } elseif (!is_array($config[$pieces[0]])) {
                    throw new Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                $config[$pieces[0]] = $this->processKey($config[$pieces[0]], $pieces[1], $value);
            } else {
                throw new Exception("Invalid key '$key'");
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }
}
