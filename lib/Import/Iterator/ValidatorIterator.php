<?php
namespace FMUP\Import\Iterator;

use FMUP\Import\Config;
use FMUP\Import\Config\ConfigObjet;
use FMUP\Import\Exception;

/**
 * Valide une ligne et compte le nombre de ligne MAJ ou CRÉÉ
 *
 * @author csanz
 *
 */
class ValidatorIterator extends \IteratorIterator
{
    /**
     * Si la ligne est validée
     *
     * @var bool
     */
    private $valid;

    /**
     * Si l'import va réaliser uniquement des insert, le type sera "insert" sinon "update"
     *
     * @var string
     */
    private $lineType;

    /**
     *
     * @var integer
     */
    private $totalInsert = 0;

    /**
     *
     * @var Integer
     */
    private $totalUpdate = 0;

    /**
     *
     * @var integer
     */
    private $totalErrors = 0;

    /*
     * ***************************
     * GETTERS
     * ***************************
     */

    /**
     *
     * @return bool
     */
    public function getValid()
    {
        return (bool)$this->valid;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->lineType;
    }

    /**
     *
     * @return int
     */
    public function getTotalUpdate()
    {
        return (int)$this->totalUpdate;
    }

    /**
     *
     * @return int
     */
    public function getTotalInsert()
    {
        return (int)$this->totalInsert;
    }

    /**
     *
     * @return int
     */
    public function getTotalErrors()
    {
        return (int)$this->totalErrors;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function current()
    {
        $current = $this->getInnerIterator()->current();
        if (!$current instanceof Config) {
            throw new Exception('Iterator can only validate Config');
        }
        $this->valid = $current->validateLine();
        $type = "";
        foreach ($current->getListeConfigObjet() as $configObject) {
            $status = $configObject->getStatut();
            if ($status == ConfigObjet::INSERT) {
                $type = ($type == ConfigObjet::UPDATE ? ConfigObjet::UPDATE : ConfigObjet::INSERT);
            } elseif ($status == ConfigObjet::UPDATE) {
                $type = ConfigObjet::UPDATE;
            }
        }
        if ($this->valid && !$current->getDoublonLigne()) {
            if ($type == ConfigObjet::INSERT) {
                $this->totalInsert++;
            } elseif ($type == ConfigObjet::UPDATE) {
                $this->totalUpdate++;
            }
        } else {
            $this->totalErrors++;
        }
        $this->lineType = $type;
        return $current;
    }
}
