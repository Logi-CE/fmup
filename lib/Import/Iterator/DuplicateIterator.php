<?php
namespace FMUP\Import\Iterator;

use FMUP\Import\Config;
use FMUP\Import\Exception;

/**
 * Permet de repérer les éventuels doublons présents dans le fichier
 *
 * @author csanz
 *
 */
class DuplicateIterator extends \IteratorIterator
{
    /**
     *
     * @var int[]
     */
    private $keyList = array();
    private $currentKey = null;

    /**
     * (non-PHPdoc)
     *
     * @see IteratorIterator::next()
     * @return mixed
     * @throws Exception
     */
    public function current()
    {
        $current = $this->getInnerIterator()->current();
        $currentKey = $this->getInnerIterator()->key();
        if ($this->currentKey !== $currentKey) {
            $this->currentKey = $currentKey;
            if (!$current instanceof Config) {
                throw new Exception('Current object is not config');
            }
            $this->checkDuplicate($current);
        }
        return $current;
    }

    /**
     * Vérifie l'unicité des objets de la ligne
     *
     * @param Config $current
     * @return bool true if duplicate found
     */
    public function checkDuplicate(Config $current)
    {
        $str = '';
        // on concatène tous les champs obligatoires
        foreach ($current->getListeConfigObjet() as $configObject) {
            foreach ($configObject->getListeIndexChamp() as $index) {
                $str .= $current->getField($index)->getValue() . ';';
            }
        }
        $uniqueKey = sha1($str);
        $key = isset($this->keyList[$uniqueKey]) ? $this->keyList[$uniqueKey] : false;
        $current->setDoublonLigne($key);
        if ($key === false) {
            // si on ne le trouve pas, on l'ajoute dans le tableau
            $this->keyList[$uniqueKey] = $this->getInnerIterator()->key();
        }
        return (bool)$key;
    }
}
