<?php
/**
 * Fonction gérant les logs et l'historisation de ceux-ci
 * C'est aussi un modèle pour la table du même nom
 * @author shuet
 */
class Historisation extends Model
{
        protected $id;
        protected $id_utilisateur;
        protected $ip;
        protected $libelle;
        protected $date_action;

/* ******************
 * Requètes d'accès *
 ****************** */
        /**
         * Retourne tous les éléments éventuellement filtrés
         * @param {Array} un tableau de tous les filtres éventuels
         * @param {String} le champ sur lequel ordonner
         **/
        /*static function findAll($where = array(), $options = array()) {
            $result = Model::findAllFromTable('HISTORISATIONS', $where, $options);
            // Création d'un tableau d'objets
            return Model::objectsFromMatrix($result, "Historisation");
        }*/

        /*public static function getQueryString()
        {
            return "select * from HISTORISATIONS";
        }*/

        /**
         * Retourne un élément
         * @param {Integer} un identifiant
         **/
        public static function findOne($id, $options = array())
        {
            $return = Historisation::findAll(array('id = '.Sql::secureId($id)), $options);
            if (count($return)>0) {
                return $return[0];
            } else {
                return null;
            }
        }

        /**
         * Retourne le nombre d'éléments d'une requète
         * @param {Array} un tableau de condititions
         **/
        public static function count($where = array(), $options = array())
        {
            //return Model::countFromTable('HISTORISATIONS', $where);

            if (isset($options['count']) && $options['count'] == true) {
                $SQL = "SELECT count(*) as nb FROM HISTORISATIONS";
                $SQL .= sql::ParseWhere($where);
                if (isset($options["group_by"]) && $options["group_by"]) {
                    $SQL .= " group by ".$options["group_by"];
                }
            } else {
                $SQL = 'SELECT FOUND_ROWS() as nb';
            }

            // Exécution de la requète
            $db = \Model::getDb();
            if (!$db instanceof \FMUP\Db) {
                $result = $db->requete($SQL);
            } else {
                $result = $db->fetchAll($SQL);
            }
            return $result[0]["nb"];
        }

        /**
         * Retourne le premier élément
         * @param {Array} un tableau de tous les filtres éventuels
         * @param {String} le champ sur lequel ordonner
         **/
        public static function findFirst($where = array(), $order = '', $options = array())
        {
            $array = array( 'order' => $order, 'top' => '0, 1');
            $result = Historisation::findAll($where, $array);
            if (count($result)) {
                return $result[0];
            } else {
                return false;
            }
        }

/* *******************
 * Requètes de modif *
 ******************* */
        /**
         * Sauvegarder l'objet dans la base de données
         **/
        protected function insert()
        {
            $SQL = "INSERT INTO HISTORISATIONS (
                        id_utilisateur,
                        ip,
                        libelle,
                        date_action
                    ) VALUES (
                        ".Sql::secureId($this->id_utilisateur).",
                        ".Sql::secureInteger($this->ip).",
                        ".Sql::secure($this->libelle).",
                        ".Sql::secureDate($this->date_action)."
                    )";
            Controller::setFlash(Model::getMessageInsertionOK());
            return Model::getDb()->execute($SQL);
        }

        /**
         * Mettre à jour l'objet dans la base de données
         **/
        protected function update()
        {
            $SQL = "UPDATE HISTORISATIONS SET
                        id_utilisateur = ".Sql::secureId($this->id_utilisateur).",
                        ip             = ".Sql::secureInteger($this->ip).",
                        libelle        = ".Sql::secure($this->libelle).",
                        date_action    = ".Sql::secureDate($this->date_action)."
                    WHERE id = ".$this->id;
            Controller::setFlash(Model::getMessageUpdateOK());
            return Model::getDb()->execute($SQL);
        }

        /**
         * Supprime l'objet
         **/
        public function delete($delete = false)
        {
            return $this->deleteFromTable('HISTORISATIONS');

        }

/* ***********************
 * Accesseurs d'attribut *
 *********************** */

    /**
     * Retourne le champ date_action
     **/
    public function getDateAction()
    {
        return Date::ukToFr($this->date_action);
    }

    /**
     * Modifie le champ date_action
     **/
    public function setDateAction($value)
    {
        $this->date_action = Date::frToSql($value);
        return true;
    }

    /**
     * Retourne le champ ip
     **/
    public function getIp()
    {
        return long2ip($this->ip);
    }

    /**
     * Modifie le champ ip
     **/
    public function setIp($value)
    {
        $this->ip = ip2long($value);
        return true;
    }

/* ********************
 * Accesseurs d'objet *
 ******************** */
        /**
         * Retourne le champ utilisateur
         **/
        public function getUtilisateur ()
        {
            $utilisateur = Utilisateur::findOne($this->id_utilisateur);
            if (!$utilisateur) {
                $utilisateur = new Utilisateur();
            }
            return $utilisateur;
        }

/* *********************
 *  Sécurisation des   *
 *      éditions       *
 ***********************/

     /**
      * Renvoi le tableau des champs autorisés
      * pour l'utilisateur en cours
      *
      * @return tableau des champs autorisés
      */
    public function listeChampsModifiable()
    {
        $champs = array();

        $champs['id_utilisateur'] = 'id_utilisateur';
        $champs['ip'] = 'ip';
        $champs['date_action'] = 'date_action';

        return $champs;
     }

    /**
     * Retourne un tableau des alias des champs associés aux champs auxquels il correspondent
     * @return {Array} tableau des champs renommés
     */
    public static function XFields()
    {
        $fields = array (

        );
        return $fields;
    }

    /**
     * fonction permettant d'initialiser certaines checkbox sur la vue
     */
    public function initCheckboxes()
    {
        //if (!isset($_POST["historisation"]['champ'])) 					$_POST["historisation"]['champ'] 	= 0;
    }


/* ***********************
 * Fonctions spécifiques *
 *********************** */

    public static function init($libelle = "")
    {
        $historisation = new Historisation();
        $historisation->setLibelle($libelle);
        $historisation->save();
        if ($historisation && !self::getIdHistoCourant()) {
            $_SESSION["id_historisation"] = $historisation->getId();
        }
    }

    public static function getIdHistoCourant()
    {
        return (!empty($_SESSION["id_historisation"])) ? $_SESSION["id_historisation"] : "";
    }

    public static function destroy()
    {
        $_SESSION["id_historisation"] = "";
        unset($_SESSION["id_historisation"]);
    }


/*******************
 * Log des actions *
 *******************/
     /**
      * Spécifie si les changements doivent être enregistrés
      * @return Booléen
      */
     public static function tableToLog()
     {
         return false;
     }

     public function getTableName()
     {
         return 'HISTORISATIONS';
     }
/* ************
 * Validation *
 ************ */
    /**
     * Autorise la suppression d'un element de la base de données
     **/
    public function canBeDeleted ()
    {
        return true;
    }

    /**
     * Initialisation de certaines variables
     */
    public function setValeursDefaut()
    {
        $this->id_utilisateur = isset($_SESSION['id_utilisateur']) ? $_SESSION['id_utilisateur'] : -1; //possible par cron
        $this->setIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'); //possible par cron
        $this->setDateAction(Date::today(true));
    }

    /**
     * Valide l'objet
     **/
    public function validate($save = false)
    {
        //$this->errors = array();
        $this->setValeursDefaut();

        if ('' == $this->id_utilisateur) {
            $this->errors["id_utilisateur"] = "Le champ 'utilisateur' doit être renseigné.";
        }
//		if ('' == $this->ip) {
//			$this->errors["ip"] = "Le champ 'ip' doit être renseigné.";
//		}
//		if ('' == $this->date_action) {
//			$this->errors["date_action"] = "Le champ 'date_action' doit être renseigné.";
//		}

        if (count($this->errors)) {
            return false;
        } else {
            return true;
        }
    }
}
