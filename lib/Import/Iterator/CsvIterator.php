<?php
namespace FMUP\Import\Iterator;

/**
 * Permet de parcourir un csv ligne par ligne
 *
 * @author jyamin
 *
 */
class CsvIterator implements \Iterator
{
    const COMMA_SEPARATOR      = ',';
    const SEMICOLON_SEPARATOR  = ';';
    const TABULATION_SEPARATOR = "\t";

    protected $path;

    private $fHandle;

    private $current;

    private $ligne;

    private $separator;

    public function __construct($path = "", $separator = "")
    {
        $this->setPath($path);
        $this->assignSeparatorForConstruct($separator);
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function rewind()
    {
        if (!file_exists($this->path)) {
            throw new \Exception("Le fichier specifie n'existe pas ou est introuvable");
        }
        $this->fHandle = fopen($this->path, "r");
        rewind($this->fHandle);
        $this->next();
        $this->ligne = 0;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {   
        $this->current = fgetcsv($this->fHandle, 0, $this->getSeparator());
        $this->ligne++;
        return $this->current;
    }

    public function valid()
    {
        if (feof($this->fHandle)) {
            fclose($this->fHandle);
            return false;
        }
        return true;
    }
    
     /**
     * Get separator from a specified file
     * @param string $file
     * @param int $checkLines
     * @return const separator | throw \LogiCE\Exception
     */
    function getSeparatorFromFile($file = "", $checkLines = 2)
    {
        if ($file == "" && (isset($this->path) && $this->path != "")) {
            $file = $this->path;
        }
        $spl_file_object = new \SplFileObject($file);

        $delimiters = array(
          self::COMMA_SEPARATOR,
          self::TABULATION_SEPARATOR,
          self::SEMICOLON_SEPARATOR
        );
        $results = array();
        for($i = 0; $i <= $checkLines; $i++){
            $line = $spl_file_object->fgets();
            foreach ($delimiters as $delimiter){
                $regExp = '/['.$delimiter.']/';
                $fields = preg_split($regExp, $line);
                if(count($fields) > 1){
                    if(!empty($results[$delimiter])){
                        $results[$delimiter]++;
                    } else {
                        $results[$delimiter] = 1;
                    }   
                }
            }
        }
        $results = array_keys($results, max($results));

            if ($results[0] == null) {
                throw new \LogiCE\Exception("le format du séparateur dans le fichier csv importé n'est pas valide");
            }
        
        return $results[0];
    }

    /**
     * Get separator
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Set separator
     * @param string $separator
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }
    
     /**
     * Assign separator for constructor
     * @param string $separator
     * @return self
     */
    private function assignSeparatorForConstruct($separator = "") {
        if ($separator == "") {
            $this->setSeparator($this->getSeparatorFromFile());
        } else {
            $this->setSeparator($separator);
        }
        return $this;
    }

    public function key()
    {
        return $this->ligne;
    }
}
