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
    const COMMA_SEPARATOR     = ',';
    const SEMICOLON_SEPARATOR = ';';

    protected $path;

    private $fHandle;

    private $current;

    private $ligne;

    private $separator;

    public function __construct($path = "", $separator = self::SEMICOLON_SEPARATOR)
    {
        $this->setPath($path);
        $this->setSeparator($separator);
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

    public function key()
    {
        return $this->ligne;
    }
}
