<?php
/**
 * ConfigObjet.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Import\Config\Field\Validator;


class ConfigObjetTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $config = new \FMUP\Import\Config\ConfigObjet('objectName', 1);
        $this->assertSame('objectName', $config->getNomObjet());
        $this->assertSame(1, $config->getPriorite());
        $this->assertSame(array(''), $config->getIdNecessaire());
        $this->assertSame(array('' => 'id_'), $config->getNomAttribut());

        $config = new \FMUP\Import\Config\ConfigObjet('objectName2', 2, 'test;value');
        $this->assertSame('objectName2', $config->getNomObjet());
        $this->assertSame(2, $config->getPriorite());
        $this->assertSame(array('test', 'value'), $config->getIdNecessaire());
        $this->assertSame(array('test' => 'id_Test', 'value' => 'id_Value'), $config->getNomAttribut());
    }

    public function testSetGetNomAttribut()
    {
        $config = new \FMUP\Import\Config\ConfigObjet('objectName2', 2, 'test;value');
        $this->assertSame(array('test' => 'id_Test', 'value' => 'id_Value'), $config->getNomAttribut());
        $this->assertSame($config, $config->setNomAttribut('test', 'value2'));
        $this->assertSame(array('test' => 'value2', 'value' => 'id_Value'), $config->getNomAttribut());
        $this->assertSame($config, $config->setNomAttribut('untest', 'value3'));
        $this->assertSame(array('test' => 'value2', 'value' => 'id_Value', 'untest' => 'value3'), $config->getNomAttribut());
    }

    public function testAddIndexGetListeIndexChamp()
    {
        $config = new \FMUP\Import\Config\ConfigObjet('objectName2', 2);
        $this->assertSame(array(), $config->getListeIndexChamp());
        $this->assertSame($config, $config->addIndex(8));
        $this->assertSame($config, $config->addIndex(5));
        $this->assertSame($config, $config->addIndex(12));
        $this->assertSame(array(8, 5, 12), $config->getListeIndexChamp());
    }

    public function testGetSetStatus()
    {
        $config = new \FMUP\Import\Config\ConfigObjet('objectName2', 2);
        $this->assertSame("", $config->getStatut());
        $this->assertSame($config, $config->setStatutInsertion());
        $this->assertSame($config::INSERT, $config->getStatut());
        $this->assertSame($config, $config->setStatutMaj());
        $this->assertSame($config::UPDATE, $config->getStatut());
    }
}
