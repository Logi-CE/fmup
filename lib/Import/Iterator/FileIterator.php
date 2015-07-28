<?php
namespace FMUP\Import\Iterator;

/**
 * Permet de parcourir un fichier ligne par ligne
 * 
 * @author csanz
 *        
 */
class FileIterator implements \Iterator
{

    protected $path;

    private $fHandle;

    private $current;

    private $ligne;

    public function __construct($path = "")
    {
        $this->setPath($path);
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function rewind()
    {
        if (! file_exists($this->path)) {
            throw new \Exception("Le fichier specifie n'existe pas ou est introuvable");
        }
        $this->ligne = 0;
        $this->fHandle = fopen($this->path, "r");
        rewind($this->fHandle);
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        $this->current = fgets($this->fHandle);
        $this->ligne ++;
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

    public function key()
    {
        return $this->ligne;
    }
}