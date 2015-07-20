<?php
/**
 * Contient les paramètres généraux de l'application
 * @version 1.0
 */
class ParametreHelper
{
    
    static protected $instance;
	
	protected $liste;
	protected $db;
	
	/**
	 * On instancie une connexion avec la base puisque cette classe est appelée très tôt dans le framework
	 */
	protected function __construct()
	{
	    $param = Config::parametresConnexionDb();
        switch ($param['driver']) {
            case 'mysql':
                $this->db = DbConnectionMysql::getInstance(Config::parametresConnexionDb());
                break;
            case 'mssql':
                $this->db = new DbConnectionMssql(Config::parametresConnexionDb());
                break;
            default:
                new Error('Moteur de base de données non paramétré');
        }
	}
	
	public static function getInstance ()
	{
	    if (!isset(self::$instance)) {
	        self::$instance = new ParametreHelper();
	    }
	    return self::$instance;
	}

	/**
	 * Fonction pré-chargeant les données pour l'utilisation
	 * Elle nous permettra de ne lancer qu'une requête puisqu'on gardera les résultats
	 */
	protected function charger ()
	{
		$sql = 'SELECT nom, valeur FROM parametre';
		$result = $this->db->requete($sql);
		$this->liste = array();
		foreach ($result as $liste) {
			$this->liste[$liste['nom']] = $liste['valeur'];
		}
	}
	
	/**
	 * Fonction permettant de récupérer un paramètre particulier
	 * @param String $libelle : Nom du paramètre
	 */
	public function trouver ($libelle)
	{
		if ( empty($this->liste) ) {
			$this->charger();
		}
		if ( isset($this->liste[$libelle]) ) {
			$retour = $this->liste[$libelle];
		} else {
			throw new Error(Error::configParamAbsent($libelle));
		}
		return $retour;
	}
	
	/**
	 * Fonction qui modifie la valeur d'un paramètre
	 * @param String $libelle : Libellé du paramètre
	 * @param String $valeur : Nouvelle valeur du paramètre
	 */
	public function modifier ($libelle, $valeur)
	{
		$sql = 'UPDATE parametre
				SET valeur = '.Sql::secure($valeur).'
					, date_modification = NOW()
					, id_modificateur = '.Sql::secureId($_SESSION['id_utilisateur']).'
    			WHERE nom = '.Sql::secure($libelle).'
    				AND modifiable = 1';
		$result = $this->db->execute($sql);
		// Mise à jour dans le tampon
		if ( !empty($this->liste) ) {
			$this->liste[$libelle] = $valeur;
		}
	}
}
