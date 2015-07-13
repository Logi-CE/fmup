/**
 * Classe de base de <?php echo $class; ?>.
 * Classe générée automatiquement le <?php echo Date::today('FR', true)."\n"; ?>
 */
abstract class Base<?php echo $class; ?> extends Model {
<?php foreach ($champs as $champ) : ?>
	protected $<?php echo $champ; ?>;
<?php endforeach; ?>
<?php foreach ($champs_tableau as $champ) : ?>
<?php if (($champ['type_application'] == 'id')) : ?>
	protected $_<?php echo substr($champ['Field'], 3); ?> = null;
<?php endif; ?>
<?php endforeach; ?>
		
	/**
	 * Sauvegarder l'objet dans la base de données
	 * @return int L'ID inséré
	 */
	protected function insert()
	{
		$sql = "INSERT INTO ".<?php echo $class; ?>::getTableName()." (
<?php
$separateur = ' ';
foreach($champs_tableau as $champ):
	if ($champ["Field"] != 'id'): ?>
			<?php echo $separateur; ?> <?php echo $champ["Field"]; ?> 
<?php $separateur = ',';
	endif;
endforeach; ?>
		) VALUES (
<?php
$separateur = ' ';
foreach($champs_tableau as $champ):
	if ($champ["Field"] != "id"):
		if ($champ["type_application"] == 'id') : ?>
			<?php echo $separateur; ?> ".Sql::secureId($this-><?php echo $champ["Field"]; ?>)."
<?php elseif ($champ["type_application"] == 'date') : ?>
			<?php echo $separateur; ?> ".Sql::secureDate($this-><?php echo $champ["Field"]; ?>)."
<?php elseif ($champ["type_application"] == 'decimal') : ?>
			<?php echo $separateur; ?> ".Sql::secureDecimal($this-><?php echo $champ["Field"]; ?>)."
<?php elseif ($champ["type_application"] == 'integer') : ?>
			<?php echo $separateur; ?> ".Sql::secureInteger($this-><?php echo $champ["Field"]; ?>)."
<?php else : ?>
			<?php echo $separateur; ?> ".Sql::secure($this-><?php echo $champ["Field"]; ?>)."
<?php endif;
		$separateur = ',';
	endif;
endforeach; ?>
		)";
		Controller::setFlash(Constantes::getMessageFlashInsertionOk());
		return Model::getDb()->execute($sql);
	}

	/**
	 * Mettre à jour l'objet dans la base de données
	 * @return int 1 Si le traitement s'est bien passé
	 */
	protected function update()
	{
		$sql = "UPDATE ".<?php echo $class; ?>::getTableName()." SET
<?php
$separateur = ' ';
$taille_max = array_reduce($champs, create_function('$i, $j', 'return max(strlen($j), $i);'));
foreach($champs_tableau as $champ):
	if ($champ["Field"] != 'id'):
		$espaces = str_pad(' ', $taille_max - strlen($champ["Field"]));
		if ($champ["type_application"] == 'id'): ?>
            <?php echo $separateur; ?> <?php echo $champ["Field"]; ?><?php echo $espaces; ?>= ".Sql::secureId($this-><?php echo $champ["Field"]; ?>)."
<?php elseif ($champ["type_application"] == 'date'): ?>
            <?php echo $separateur; ?> <?php echo $champ["Field"]; ?><?php echo $espaces; ?>= ".Sql::secureDate($this-><?php echo $champ["Field"]; ?>)."
<?php elseif ($champ["type_application"] == 'decimal'): ?>
            <?php echo $separateur; ?> <?php echo $champ["Field"]; ?><?php echo $espaces; ?>= ".Sql::secureDecimal($this-><?php echo $champ["Field"]; ?>)."
<?php elseif ($champ["type_application"] == 'integer'): ?>
            <?php echo $separateur; ?> <?php echo $champ["Field"]; ?><?php echo $espaces; ?>= ".Sql::secureInteger($this-><?php echo $champ["Field"]; ?>)."
<?php else: ?>
            <?php echo $separateur; ?> <?php echo $champ["Field"]; ?><?php echo $espaces; ?>= ".Sql::secure($this-><?php echo $champ["Field"]; ?>)."
<?php endif;
		$separateur = ',';
	endif;
endforeach;
?>	  	WHERE id = ".Sql::secureId($this->id);
		Controller::setFlash(Constantes::getMessageFlashModificationOk());
		return Model::getDb()->execute($sql);
	}

<?php foreach($champs_tableau as $champ): ?>
<?php if ($champ['type_application'] == 'date' && $champ['Field'] != 'date_creation' && $champ['Field'] != 'date_modification') : ?> 
	/**
	 * Retourne le champ <?php echo $champ['Field']; ?> 
	 * @return <?php echo $champ['Field']; ?> 
	 */
	public function get<?php echo String::toCamlCase($champ['Field']); ?>()
	{
		return Date::ukToFr($this-><?php echo $champ['Field']; ?>);
	}

	/**
	 * Modifie le champ <?php echo $champ['Field']; ?> 
	 * @param $champ La valeur à utiliser
	 * @return true
	 */
	public function set<?php echo String::toCamlCase($champ['Field']); ?>($value)
	{
		$this-><?php echo $champ['Field']; ?> = Date::frToSql($value);
		return true;
	}
<?php endif; ?>
<?php endforeach; ?>

<?php foreach ($champs_tableau as $champ) :
	if (($champ['type_application'] == 'id')) :
		$classe_champ = String::toCamlCase(substr($champ['Field'], 3));
		if (in_array($classe_champ, array('Createur', 'Modificateur', 'Suppresseur'))) {
			$classe_champ = 'Utilisateur';
		}
?>
	/**
	 * Retourne l'objet <?php echo $classe_champ; ?> associé au champ <?php echo substr($champ['Field'], 3); ?> 
	 * @return <?php echo $classe_champ; ?> 
	 */
	public function get<?php echo String::toCamlCase(substr($champ['Field'], 3)); ?> ()
	{
		if ($this->_<?php echo substr($champ['Field'], 3); ?> === null) {
    		$this->_<?php echo strtolower(substr($champ['Field'], 3)); ?> = <?php echo $classe_champ; ?>::findOne($this-><?php echo $champ['Field']; ?>);
    		if (!$this->_<?php echo strtolower(substr($champ['Field'], 3)); ?>) {
    			$this->_<?php echo strtolower(substr($champ['Field'], 3)); ?> = new <?php echo $classe_champ; ?>();
    		}
    	}
		return $this->_<?php echo strtolower(substr($champ['Field'], 3)); ?>;
	}
	
<?php endif; ?>
<?php endforeach; ?>

    /**
     * Réinitialise les objets liés
     */
    public function reinitialiserObjets ()
    {
<?php foreach ($champs_tableau as $champ) : ?>
<?php if (($champ['type_application'] == 'id')) : ?>
	   $this->_<?php echo substr($champ['Field'], 3); ?> = null;
<?php endif; ?>
<?php endforeach; ?>
    }

<?php foreach ($tables_liees as $numero => $nom_table) : ?>
	/**
	 * Retourne une liste d'objets <?php echo String::toCamlCase($nom_table); ?> contenant le champ id_<?php echo $table; ?> 
	 * @param array $clauses : [OPT] Tableau de clauses supplémentaires
	 * @param array $option : [OPT] Tableau d'options supplémentaires
	 * @return array[<?php echo String::toCamlCase($nom_table); ?>]
	 */
	public function getList<?php echo String::toCamlCase($nom_table); ?> ($clauses = array(), $options = array())
	{
		$clauses = array_merge($clauses, array("id_<?php echo $table; ?> = ".Sql::secureId($this->id)));
		return <?php echo String::toCamlCase($nom_table); ?>::findAll($clauses, $options = array());
	}
<?php endforeach; ?>
	
<?php if ($logguer) : ?>
	/**
	 * Liste des champs ou l'on va logguer les changements entre les versions
	 * @return array
	 */
	public static function listeChampsObjet ()
	{
		return implode(', ', array_keys(self::getDictionnaire()));
	}
	
	/**
     * fonction permettant de récupérer les historiques d'un objet
     */
    public function getHistoriqueSurObjet()
    {
        $sql = "SELECT T.*, CONCAT(U.prenom, ' ', U.nom) AS user_action
                FROM log__".<?php echo $class; ?>::getTableName()." T
                INNER JOIN ".Utilisateur::getTableName()." U
                	ON U.id = T.id_utilisateur_log
                WHERE T.id_objet_log = ".Sql::secureId($this->id)."
                	AND T.contenu_log != ''
                ORDER BY T.id";
        $res = Model::getDb()->requete($sql);

        return $res;
    }
<?php endif; ?>

	/**
	 * Fonction répertoriant les champs de la table, et retournant un tableau avec les données du champ demandé
	 * @param string $nom_champ	nom du champ
	 * @return array Tableau contenant les information du champ $nom_champ
	 */
	public static function getDictionnaire($nom_champ = false) {
		$retour = array();
<?php foreach($champs_tableau as $champ) :
    $type = '';
    $length = '""';
    $unsigned = (strpos($champ['Type'], 'unsigned')) ? 'true' : 'false';
    if (preg_match('|^([a-z]*)\(?(\d*)?\)?$|', $champ['Type'], $matches)) {
    	$type = $matches[1];
    	$length = (!empty($matches[2])) ? $matches[2] : '""';
    } elseif (preg_match('|^(enum)\((.+)\)$|', $champ['Type'], $matches)) {
    	$type = $matches[1];
    	$values = preg_replace('#\'(?:[^\'\\\\]|\\\\\')*\'#' ,'$0=>$0', $matches[2]);
    }
?>
			$retour["<?php echo $champ['Field'] ?>"] = array (
    			  'type' => '<?php $type; ?>'
    			, 'taille' => <?php echo $length; ?> 
    			, 'defaut' => '<?php echo $champ['Default']; ?>'
    			, 'unsigned' => <?php echo $unsigned; ?> 
    			, 'null' => <?php echo ($champ['Null'] == 'YES') ? 'true' : 'false'; ?> 
			);
<?php endforeach; ?>
		if (isset($retour[$nom_champ])) {
			$retour = $retour[$nom_champ];
		}
		
		return $retour;
	}
	
	/**
     * Retourne un tableau des alias des champs associés aux champs auxquels il correspondent
     * @return {Array} tableau des champs renommés
     */
    public static function xFields()
    {
        $fields = array();
        foreach (<?php echo $class; ?>::getXFields() as $cle => $valeur) {
            $fields['#(^|[^._a-z])'.$cle.'([^_]|$)#'] = '$1'.$valeur.'$2';
        }
        foreach (<?php echo $class; ?>::getDictionnaire() as $cle => $valeur) {
            $fields['#(^|[^._a-z])('.$cle.')([^_]|$)#'] = '$1'.self::getAliasTable().'.$2$3';
        }

        return $fields;
    }
	
	protected static function getXFields()
	{
		return array();
	}
	
	protected static function getAliasTable()
	{
		return strtoupper(substr(self::getTableName(), 0, 1));
	}

	/**
	 * Renvoi le tableau des champs autorisés pour l'utilisateur en cours
	 * @return tableau des champs autorisés
	 */
	public function listeChampsModifiable()
	{
		$champs = array();
		switch (Utilisateur::getIdNatureConnecte()) {
			default :
<?php foreach ($champs as $champ) : ?>
				$champs['<?php echo $champ; ?>'] = '<?php echo $champ; ?>';
<?php endforeach; ?>
				break;
		}
		return $champs;
	}

	/**
	 * Spécifie si les changements doivent être enregistrés
	 * @return bool
	 */
	public static function tableToLog()
	{
		return <?php echo ($logguer) ? 'true' : 'false'; ?>;
	}

	/**
	 * Nom de la table en base
	 * @return string
	 */
	public static function getTableName()
	{
		return '<?php echo $table; ?>';
	}
	
<?php if (!$suppression_physique) : ?>
	/**
	 * Détermine si les éléments supprimés sont affichés dans les listes
	 * @return bool
	 */
	public static function afficherParDefautNonSupprimes()
	{
		return true;
	}
<?php endif; ?>
	
/* ************
 * Validation *
 ************ */
	/**
	 * Autorise la suppression d'un element de la base de données
	 * Si la fonction trouve des erreurs, elles seront répertoriées dans la variable erreur
	 * @return bool : VRAI si la suppression est autorisée
	 */
	public function canBeDeleted()
	{
<?php foreach ($tables_liees as $table_liee) : ?>
		if ( count($this->getList<?php echo String::toCamlCase($table_liee); ?>()) > 0 ) {
			array_push($this->errors, "Cet objet est lié à la table <?php echo $table_liee; ?>.");
		}
<?php endforeach; ?>

<?php if (!empty($tables_liees)) : ?>
		if ( count($this->errors) ) {
			return false;
		} else {
			return true;
		}
<?php else : ?>
		return true;
<?php endif; ?>
	}

	/**
	 * Initialisation de certaines variables
	 * Gestion du créateur et du dernier modificateur (si presents)
	 * @return void
	 */
	public function setValeursDefaut()
	{
<?php if (in_array('id_createur', $champs) || in_array('date_creation', $champs)) : ?>
		//if (!$this->id) {
<?php if (in_array('id_createur', $champs)) : ?>
			if ( '' == $this->id_createur ) {
				$this->setIdCreateur();
			}
<?php endif; ?>
<?php if (in_array('date_creation', $champs)) : ?>
			if ( '' == $this->date_creation ) {
				$this->setDateCreation();
			}
<?php endif; ?>
		//}
<?php endif; ?>
<?php if (in_array('id_modificateur', $champs)) : ?>
		$this->setIdModificateur();
<?php endif; ?>
<?php if (in_array('date_modification', $champs)) : ?>
		$this->setDateModification();
<?php endif; ?>
<?php foreach ($champs_tableau as $champ) : ?>
<?php if ($champ['Default'] != '') : ?>
	
		if( '' === $this-><?php echo $champ['Field']; ?> || null === $this-><?php echo $champ['Field']; ?> ) {
			$this->set<?php echo String::toCamlCase($champ['Field']); ?>("<?php echo $champ['Default']; ?>");
		}
<?php endif; ?>
<?php endforeach; ?>
	}

	/**
	 * Valide l'objet
	 * Si la fonction trouve des erreurs, elles seront répertoriées dans la variable erreur
	 * @return bool : VRAI si l'enregistrement est autorisée
	 */
	public function validate($save = false)
	{
		$this->setValeursDefaut();

<?php foreach($champs_tableau as $champ) :
	$verif = false;
	if ($champ['Field'] != 'id') :
		if ($champ['type_application'] == 'date') :
            if ($params_connexion['driver'] == 'mysql') :
                $verif = 'dateTimeUk';
            elseif ($params_connexion['driver'] == 'mssql') :
                $verif = 'dateTime';
            endif;
		elseif ($champ['type_application'] == 'integer'):
			$verif = 'integer';
		endif;
?>
<?php if ($champ['Null'] == 'NO'): ?>
		if ('' === $this-><?php echo $champ['Field']; ?> || null === $this-><?php echo $champ['Field']; ?>) {
<?php if (false !== strpos($champ['Field'], 'id_')): ?>
			$this->errors["<?php echo $champ['Field']; ?>"] = "Le champ '<?php echo preg_replace("/^id_/", "", $champ['Field']); ?>' doit être renseigné.";
<?php else: ?>
			$this->errors["<?php echo $champ['Field']; ?>"] = "Le champ '<?php echo $champ['Field']; ?>' ne doit pas être vide.";
<?php endif; ?>
<?php endif; ?>
<?php if ($verif && $champ['Null'] == 'NO') : ?>
		} elseif (!Is::<?php echo $verif; ?>($this-><?php echo $champ['Field']; ?>)) {
			$this->errors["<?php echo $champ['Field']; ?>"] = "Le champ '<?php echo $champ['Field']; ?>' n'est pas correct.";
		}
<?php elseif ($verif) : ?>
		if ($this-><?php echo $champ['Field']; ?> && !Is::<?php echo $verif; ?>($this-><?php echo $champ['Field']; ?>)) {
			$this->errors["<?php echo $champ['Field']; ?>"] = "Le champ '<?php echo $champ['Field']; ?>' n'est pas correct.";
		}
<?php elseif ($champ['Null'] == 'NO') : ?>
		}
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
		if (count($this->errors)) {
			return false;
		} else {
			return true;
		}
	}
}