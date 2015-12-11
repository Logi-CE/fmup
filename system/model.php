<?php

/**
 * Classe mère de tous les modèles
 * @version 1.0
 */
abstract class Model
{
    public static $is_logue = false;
    protected static $dbInstance = null;
    protected $errors = array();
    protected $nombre_ligne; // pour l'affichage du nombre de lignes des listes (utilisés dans les Xobjets)
    protected $log_id;
    public $requete;
    public static $nb_elements;

    public function getIsLogue()
    {
        return (bool) self::$is_logue;
    }

    public function setIsLogue($bool = false)
    {
        self::$is_logue = (bool) $bool;
        return $this;
    }

    /* **********
    * Créateur	*
    ********** */
    /**
     * Crée une instance de classe avec les paramètres transmis
     * Typiquement $_REQUEST d'un formulaire
     **/
    public function __construct($params = array())
    {
        $this->initialisationVariablesAvantContructeur();
        foreach (array_keys(get_object_vars($this)) as $attribute) {
            if (isset($params[$attribute])) {
                $this->setAttribute($attribute, $params[$attribute]);
            }
        }
        return $this;
    }

    public function getAttributes()
    {
        return array_keys(get_object_vars($this));
    }

    /**
     * cette fonction est appelée au tout début du constructeur.
     * elle est principalement utilisée pour la gestion des cases à cocher qui doivent être décochée
     * si elles ne sont pas envoyée dans le $_POST du formulaire.
     */
    public function initialisationVariablesAvantContructeur()
    {
        /* fonction à redéfinir dans le modèle au besoin */
    }

    /**
     * Retourne le nom du controller associé à la classe appelante
     * @return string : Le nom du controleur
     */
    public static function getControllerName()
    {
        $classe_appelante = get_called_class();
        if (substr($classe_appelante, 0, 4) == 'Base') {
            $classe_appelante = substr($classe_appelante, 4);
        }
        return \FMUP\String::getInstance()->toCamelCase($classe_appelante);
    }

    /**
     * transforme un objet en tableau
     */
    public function objectToTable($objet)
    {
        $tableau = array();
        foreach ($objet as $attribute => $value) {
            $tableau[$attribute] = $value;
        }
        return $tableau;
    }

    /**
     * transforme un objet en tableau sans recupérer l'id
     */
    public function objectToTableSansId($objet)
    {
        $tableau = array();
        foreach ($objet as $attribute => $value) {
            if ($attribute != "id") {
                $tableau[$attribute] = $value;
            }
        }
        return $tableau;
    }

    /**
     * Crée une instance d'un modèle avec les paramètres transmis
     * Typiquement le résultat d'une requète
     * @param array $params : Données de l'objet
     * @param string $class_name : Type d'objet
     * @return Object
     */
    private static function create($params, $class_name)
    {
        $driver = Config::parametresConnexionDb('driver');
        $class = new $class_name();
        foreach ($params as $attribut => $value) {
            if (substr($attribut, 0, 5) == 'date_' && $driver == 'mssql') {
                $class->$attribut = Date::ukToFr(substr($value, 0, 19));
            } else {
                $class->$attribut = $value;
            }
        }
        return $class;
    }

    /* *************************
    * Affichage et convertion *
    ************************* */
    /**
     * Retourne une classe sous forme de chaîne
     **/
    public function __toString()
    {
        ob_start();
        var_dump($this);
        return ob_get_clean();
    }

    /**
     * Convertit une collection en tableau
     * @param {Array} La collection
     * @param {Integer} L'attribut à mettre dans les index du tableau
     * @param {Integer} L'attribut ou les attributs (tableau) à mettre dans les valeurs du tableau
     **/
    public static function arrayFromCollection($collection, $element_value, $element_text)
    {
        $array = array();
        foreach ($collection as $element) {
            if (is_array($element_text)) {
                $liste_attributs = array();
                foreach ($element_text as $attribut) {
                    $liste_attributs[] = $element->getAttribute($attribut);
                }
                $array[$element->getAttribute($element_value)] = implode(' - ', $liste_attributs);
            } else {
                $array[$element->getAttribute($element_value)] = $element->getAttribute($element_text);
            }
        }
        return $array;
    }

    /**
     * Convertit un objet en tableau
     * @param {Array} La collection
     **/
    public function arrayFromObject()
    {
        $array = array();
        foreach (array_keys(get_object_vars($this)) as $attribute) {
            $array[$attribute] = $this->getAttribute($attribute);
        }
        return $array;
    }

    /**
     * Crée des objets à partir d'une matrice (typiquement le résultat d'une requète)
     */
    protected static function objectsFromMatrix($matrix, $class_name)
    {
        $liste = array();
        if (!empty($matrix) && (is_array($matrix) || $matrix instanceof Traversable)) {
            foreach ($matrix as $array) {
                array_push($liste, Model::create($array, $class_name));
            }
        }
        return $liste;
    }

    protected static function objectsFromArray($array, $class_name)
    {
        return Model::create($array, $class_name);
    }

    protected static function objectsFromMatrixByAttribute($matrix, $class_name, $attribute = 'id')
    {
        $liste = array();
        if (!empty($matrix) && (is_array($matrix) || $matrix instanceof Traversable)) {
            foreach ($matrix as $array) {
                $objet = Model::create($array, $class_name);
                $liste[$objet->getAttribute($attribute)] = $objet;
            }
        }
        return $liste;
    }

    /**
     * Retourne tous les éléments éventuellement filtrés de la table
     * @param array $where : [OPT] Un tableau de tous les filtres éventuels
     * @param array $options : [OPT] Options disponibles :
     *        - order : Tri sur la requête
     *        - findOne : Utilisé pour déterminer qu'on ne recherche qu'un résultat
     *          (TODO pour afficher les objets supprimés, ça ressemble à un doublon, fonctionnement ésotérique...)
     *        - afficher_supprimes : TODO vérifier si c'est vraiment utilisé, apparement non
     *    Seulement MySQL
     *        - limit : Pagination
     *    Seulement MSSQL
     *        - top : Semi-pagination qui ne passe pas par la sous-vue
     *        - paging : C'est un tableau composé de numero_page et nb_element
     *                     Utilisé pour simuler une pagination grace à une sous-vue
     * @return array[object]
     */
    public static function findAll($where = array(), $options = array())
    {
        $classe_appelante = get_called_class();
        $driver = Config::parametresConnexionDb('driver');

        //si on appelle depuis un object complexe, on recupere la requete correspondante
        if (method_exists($classe_appelante, 'getQueryString')) {
            // gestion des affichages par défaut, sauf si on appelle un objet bien particulier, via la fonction findone
            if (!isset($options['fonction']) || $options['fonction'] != 'findOne') {
                // On retire les éléments supprimés de la liste
                if (call_user_func(array($classe_appelante, 'afficherParDefautNonSupprimes'))) {
                    if (!isset($where['date_suppression'])) {
                        if ($driver == 'mssql') {
                            $where['date_suppression'] = "ISnull(date_suppression, '') = ''";
                        } else {
                            $where['supprime'] = "supprime = 0";
                        }
                    } elseif (!isset($options['afficher_supprimes'])) {
                        // cette option va permettre de gérer les date de suppression dans les fonction getQueryString
                        $options['afficher_supprimes'] = true;
                    }
                }
                // On retire les éléments non visibles de la liste
                if (call_user_func(array($classe_appelante, 'afficherParDefautDataVisibles'))) {
                    if (!isset($where['visible']) && !isset($where['identifiant'])) {
                        if ($driver == 'mssql') {
                            $where['visible'] = 'ISnull(visible, 0) = 1';
                        } else {
                            $where['visible'] = 'visible = 1';
                        }
                    }
                }
            }

            $SQL = call_user_func(array($classe_appelante, 'getQueryString'), $options);
            $top = '';
            $orderby = '';
            $limit = '';

            if (isset($options["paging"]) && $driver == 'mssql') {
                // Ordre by spécialisé pour la sous-vue générée
                if (isset($options["order"]) && $options["order"] != '') {
                    $orderby = 'ORDER BY ' . $options["order"];
                } else {
                    $orderby = 'ORDER BY getdate()';
                }

                $SQL = 'WITH x AS
                        (
                            SELECT REQUETE_NUMERO_LIGNE = ROW_NUMBER() OVER (' . $orderby . ')
                                    , V.*
                            FROM
                            (
                                ' . $SQL . '
                            ) V
                            ' . Sql::parseWhere($where) . '
                        )
                        SELECT  (SELECT COUNT(*) FROM x ) AS REQUETE_NOMBRE_LIGNE, *
                        FROM x
                        WHERE x.REQUETE_NUMERO_LIGNE BETWEEN (((' .
                    $options["paging"]["numero_page"] . ' - 1) * ' . $options["paging"]["nb_element"] . ') + 1)
                            AND ' . $options["paging"]["numero_page"] . ' * ' . $options["paging"]["nb_element"] . '';

            } else {
                if (!empty($options["order"])) {
                    $orderby = " ORDER BY " . $options["order"];
                }

                if ($driver == 'mysql') {
                    $SQL .= Sql::parseWhere($where, false, $classe_appelante);
                    $SQL .= ' ' . $orderby;
                    if (!empty($options["limit"])) {
                        $SQL .= ' ' . " LIMIT " . $options["limit"];
                    }

                } elseif ($driver == 'mssql') {
                    if (!empty($options["top"])) {
                        $top = " top " . $options["top"];
                    }
                    $SQL = 'SELECT ' . $top . ' * FROM (' . $SQL . ') V ';
                    $SQL .= Sql::parseWhere($where);
                    $SQL .= ' ' . $orderby;
                }
            }
            // if ($classe_appelante == 'Facture')
            // debug::output($SQL, true);

            // Exécution de la requète
            $db = \Model::getDb();
            if (!$db instanceof \FMUP\Db) {
                $result = $db->requete($SQL);
            } else {
                $result = $db->fetchAll($SQL);
            }
        } else {
            if (call_user_func(array($classe_appelante, 'afficherParDefautNonSupprimes'))) {
                if (!isset($where['supprime']) &&
                    (!isset($options['fonction']) || $options['fonction'] != 'findOne')
                ) {
                    $where['supprime'] = 'supprime = 0';
                }
            }
            //sinon appelle de l'objet de Base généré par le génératOR
            $result = Model::findAllFromTable(
                call_user_func(array($classe_appelante, 'getTableName')),
                $where,
                $options
            );
        }

        // On ajoute dans une variable le nombre d'éléments de la dernière requête s'il n'y avait pas eu de limit
        // (pour la pagination)
        if ($driver == 'mysql') {
            $db = Model::getDb();
            if (!$db instanceof \FMUP\Db) {
                self::$nb_elements = $db->requeteUneLigne('SELECT FOUND_ROWS()');
            } else {
                self::$nb_elements = $db->fetchRow('SELECT FOUND_ROWS()');
            }
            self::$nb_elements = self::$nb_elements['FOUND_ROWS()'];
        }

        // Création d'un tableau d'objets
        return Model::objectsFromMatrix($result, $classe_appelante);
    }

    /**
     * Retourne un élément grâce à son ID
     * @param int $id : Un identifiant
     * @return null|object
     */
    public static function findOne($id)
    {
        $classe_appelante = get_called_class();

        $return = call_user_func(
            array($classe_appelante, 'findAll'),
            array('id = ' . Sql::secureId($id)),
            array('fonction' => 'findOne')
        );
        if (count($return) > 0) {
            return $return[0];
        } else {
            return null;
        }
    }

    /**
     * Retourne le premier élément d'un findAll
     * @param array $where : [OPT] Un tableau de tous les filtres éventuels
     * @param string $order : [OPT] Le champ sur lequel ordonner
     * @return false|object
     */
    public static function findFirst($where = array(), $order = '')
    {
        $classe_appelante = get_called_class();

        $return = call_user_func(
            array($classe_appelante, 'findAll'),
            $where,
            array('order' => $order, 'limit' => '0, 1', 'top' => '1')
        );
        if (count($return)) {
            return $return[0];
        } else {
            return false;
        }
    }

    /**
     * Retourne le nombre d'éléments d'une requête
     * @param array $where : Un tableau de clauses pour la requête
     * @return int : Le nombre d'éléments
     */
    public static function count($where = array())
    {
        $classe_appelante = get_called_class();
        return Model::countFromTable(call_user_func(array($classe_appelante, 'getTableName')), $where);
    }


    /**
     * Supprime l'objet dans la base de données
     * @return bool : Le résultat du traitement, VRAI si suppression
     * @todo : Supprimer les objets liés
     */
    public function delete()
    {
        $classe_appelante = get_called_class();
        $id = $this->id;
        $retour = $this->deleteFromTable(call_user_func(array($classe_appelante, 'getTableName')));
        return $retour;
    }

    /**
     * si la table de l'objet contient un champ date_suppression,
     * et qu'il ne faut afficher que les données non supprimées par défaut
     * alors réécrire cette fonction dans l'objet avec return true
     * @return bool
     */
    public static function afficherParDefautNonSupprimes()
    {
        return false;
    }

    /**
     * si la table de l'objet contient un champ visible, et qu'il ne faut afficher que les données visibles par défaut
     * alors réécrire cette fonction dans l'objet avec return true
     * @return bool
     */
    public static function afficherParDefautDataVisibles()
    {
        return false;
    }


    /* **************************
    * Requète et SQL générique *
    ************************** */
    /**
     * Trouve tous les enregistrements d'une table donnée
     * @param string $table : Le nom de la table
     * @param array $where :Un tableau de conditions
     * @param array $options : L'ordre, le limit, ...
     * @return array : Tableau contenant les objets
     */
    protected static function findAllFromTable($table, $where = array(), $options = array())
    {
        $varconnexion = Config::parametresConnexionDb();
        $SQL = "SELECT ";
        if ($varconnexion['driver'] == 'mysql') {
            $SQL .= ' SQL_CALC_FOUND_ROWS ';
        } else {
            if (isset($options["top"]) && $options["top"]) {
                $SQL .= " TOP " . $options["top"];
            }
        }
        $SQL .= " * FROM $table";
        $SQL .= Sql::parseWhere($where);
        if (isset($options["group_by"]) && $options["group_by"]) {
            $SQL .= " group by " . $options["group_by"];
        }
        if (isset($options["order"]) && $options["order"]) {
            $SQL .= " ORDER BY " . $options["order"];
        }
        if ($varconnexion['driver'] == 'mysql') {
            if (isset($options["top"]) && $options["top"]) {
                $SQL .= " LIMIT " . $options["top"];
            } elseif (!empty($options['limit'])) {
                $SQL .= " LIMIT " . $options["limit"];
            }
        }

        // Exécution de la requète
        $db = \Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $result = $db->requete($SQL);
        } else {
            $result = $db->fetchAll($SQL);
        }
        return $result;
    }

    /**
     * Retourne un tableau avec l'ID de la DMD et un attribut
     *      --> utilsié pour alléger les menus déroulant de l'application
     * @param {Array} un tableau de tous les filtres éventuels
     * @param {String} le champ sur lequel ordonner
     **/
    public static function findAllAttributeFromTable($table, $attribute, $where = array(), $options = array())
    {
        $SQL = "SELECT    id\n";
        if ($attribute != 'id') {
            $SQL .= "," . $attribute . "\n";
        }
        $SQL .= "FROM $table ";
        $SQL .= Sql::parseWhere($where);

        if (isset($options["order"]) && $options["order"]) {
            $SQL .= " ORDER BY " . $options["order"];
        }
        if (isset($options["limit"]) && $options["limit"]) {
            $SQL .= " LIMIT " . $options["limit"];
        }
        //echo($SQL);
        // Exécution de la requète
        $db = \Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $result = $db->requete($SQL);
        } else {
            $result = $db->fetchAll($SQL);
        }
        return $result;
    }

    public static function findAllAttributeFromLeftJoinTable(
        $table,
        $left_table,
        $link_table,
        $attribute,
        $where = array(),
        $options = array()
    ) {
        $SQL = "SELECT $attribute \n";
        $SQL .= " FROM $table  \n LEFT JOIN $left_table  \n";
        $SQL .= " ON $link_table  \n";
        $SQL .= Sql::parseWhere($where);

        if (isset($options["order"]) && $options["order"]) {
            $SQL .= " ORDER BY " . $options["order"] . "  \n";
        }
        if (isset($options["limit"]) && $options["limit"]) {
            $SQL .= " LIMIT " . $options["limit"];
        }
        //debug::output($SQL);
        // Exécution de la requète
        $db = \Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $result = $db->requete($SQL);
        } else {
            $result = $db->fetchAll($SQL);
        }
        return $result;
    }

    /**
     * Retourne le nombre d'éléments d'une requéte pour une table donnée
     * @param {String} Le nom de la table
     * @param {Array} un tableau de condititions
     **/
    protected static function countFromTable($table, $where = array(), $options = array())
    {
        $SQL = "SELECT COUNT(*) AS nb FROM $table";
        $SQL .= sql::ParseWhere($where);
        if (!empty($options["group_by"])) {
            $SQL .= " group by " . $options["group_by"];
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
     * Retourne la somme des valeurs d'une colonne pour une table et une colonne donnée
     * @param {String} Le nom de la table
     * @param {String} le nom de la colonne à sommer
     * @param {Array} un tableau de condition
     * @param {Array} un tableau d'options
     * @return mixed La somme si pas de group by en option, un tableau de couple somme / valeur de la colonne sinon
     **/
    protected static function sumFromTable($table, $colonne, $where = array(), $options = array())
    {
        $select = "SUM($colonne) as somme";
        $group_by = "";
        if (isset($options["group_by"]) && $options["group_by"]) {
            $select .= ", $table.$colonne";
            $group_by = " GROUP BY " . $options["group_by"];
        }
        $sql = "SELECT $select FROM $table";
        $sql .= sql::ParseWhere($where);
        $sql .= $group_by;
        // Exécution de la requête
        $db = \Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $result = $db->requete($sql);
        } else {
            $result = $db->fetchAll($sql);
        }
        return ($group_by != "") ? $result : $result[0]["somme"];
    }

    /**
     * Supprime l'objet dans la base de données
     * Place le champ supprime à 1 à la place si le champ est présent dans la table
     * @param string $table : Le nom de la table
     * @return bool : VRAI si la suppression est effectuée
     */
    protected function deleteFromTable($table)
    {
        if (($this->id > 0) && ($this->canBeDeleted())) {
            // Cas ou le champ de suppression existe
            if (property_exists($this, 'supprime')) {
                $this->supprime = true;
                $infos_suppression = '';
                if (property_exists($this, 'date_suppression')) {
                    $infos_suppression .= ', date_suppression = CURRENT_TIMESTAMP()';
                    $this->date_suppression = Date::today(false, 'US');
                }
                if (property_exists($this, 'id_suppresseur')) {
                    if (isset($_SESSION['id_utilisateur'])) {
                        $id_utilisateur = $_SESSION['id_utilisateur'];
                    }
                    $infos_suppression .= ', id_suppresseur = ' . Sql::secureId($id_utilisateur);
                    $this->id_suppresseur = $id_utilisateur;
                }
                // Loger le changement
                $this->logerChangement("delete");
                $SQL = "UPDATE $table
                        SET supprime = 1
                        $infos_suppression
                        WHERE id = " . $this->id;
                $db = Model::getDb();
                if ($db instanceof \FMUP\Db) {
                    return (bool)$db->query($SQL);
                } else {
                    return (bool)$db->execute($SQL);
                }
                // Cas de la suppression physique
            } else {
                // Loger le changement
                $this->logerChangement("delete");
                $SQL = "DELETE FROM $table WHERE id = " . $this->id;
                $db = Model::getDb();
                if ($db instanceof \FMUP\Db) {
                    $return = (bool)$db->query($SQL);
                } else {
                    $return = (bool)$db->execute($SQL);
                }
                if ($return) {
                    $this->id = "";
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Retourne l'instance de base de données du controlleur actif
     * @return DbConnectionMysql|DbConnectionMssql|\FMUP\Db
     */
    public static function getDb()
    {
        if (!self::$dbInstance) {
            self::$dbInstance = \FMUP\Helper\Db::getInstance()->get();
        }
        return self::$dbInstance;
    }

    public static function setDb($dbInstance)
    {
        self::$dbInstance = $dbInstance;
    }

    /* ************************
    * Sauvegarde des données *
    ************************ */
    /**
     * Sauvegarde ou met à jour l'objet dans la base de donnée
     * @param bool $force_enregistrement
     *      si TRUE, alors le système contrepasse le VALIDATE et enregistre quand même l'objet
     *            (ATTENTION à l'utilisation de ce paramètre)
     * @return bool
     *      VRAI si une action a eu lieu en base (si la requête ne change rien ou le validate bloque, retourne FAUX)
     * @throws \FMUP\Exception
     */
    public function save($force_enregistrement = false)
    {
        // debug::output($this, true);
        if ($force_enregistrement || $this->validate(true)) {
            if (Is::id($this->id)) {
                /* Loger le changement */
                $this->logerChangement("update");
                if ($this->update() !== false) {
                    $this->comparerDifferences();
                } else {
                    throw new \FMUP\Exception("Erreur pendant l'enregistrement");
                }
            } else {
                /* Loger le changement */
                $this->id = $this->insert();
                $this->logerChangement("insert");
            }
            //appel de la fonction post SAVE, si l'enregistrement a fonctionné
            if ($this->id) {
                $this->setValeursDefautPostSave();
            }
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * Insertion d'un objet en base
     * @return bool
     */
    abstract protected function insert();

    /**
     * Mise à jour d'un objet en base
     * @return bool
     */
    abstract protected function update();


    /**
     * Le message affiché à la création
     */
    public static function getMessageInsertionOK()
    {
        return Constantes::getMessageFlashInsertionOk();
    }

    /**
     * Le message affiché à l'update
     */
    public static function getMessageUpdateOK()
    {
        return Constantes::getMessageFlashModificationOk();
    }

    /**
     * Le message affiché à la suppression
     */
    public static function getMessageSuppressionOK()
    {
        return Constantes::getMessageFlashSuppressionOk();
    }

    /**
     * Le message affiché à l'upload d'un document
     */
    public static function getMessageInsersionDocumentOK()
    {
        return Constantes::getMessageFlashEnregistrementDocumentOk();
    }


    /* ***************************************
    * gestion des créateur et modificateurs *
    *            d'objet en base            *
    *************************************** */

    /**
     * Retourne le createur de l'objet
     * @return Utilisateur
     */
    public function getCreateur()
    {
        $createur = Utilisateur::findOne($this->id_createur);
        if (!$createur) {
            $createur = new Utilisateur();
        }
        return $createur;
    }

    /**
     * Retourne le dernier modificateur de l'objet
     * @return Utilisateur
     */
    public function getModificateur()
    {
        $modificateur = Utilisateur::findOne($this->id_modificateur);
        if (!$modificateur) {
            $modificateur = new Utilisateur();
        }
        return $modificateur;
    }

    public function getDateCreationComplete()
    {
        return str_replace(' ', ' à ', $this->getDateCreation());
    }

    /*
     * retourne la concaténation de la date et l'heure de dernière modification de l'objet
     */
    public function getDateModificationComplete()
    {
        return str_replace(' ', ' à ', $this->getDateModification());
    }

    /**
     * Retourne le champ date_creation
     **/
    public function getDateCreation()
    {
        return Date::ukToFr($this->date_creation);
    }

    /**
     * Modifie le champ date_creation par la date actuelle
     **/
    public function setDateCreation($value = '')
    {
        if ($value) {
            $this->date_creation = Date::frToSql($value);
        } else {
            $this->date_creation = Date::frToSql(Date::today(true));
        }
        return true;
    }

    /**
     * Modifie le champ createur par la personne connectée
     **/
    public function setIdCreateur($value = 0)
    {
        if ($value) {
            $this->id_createur = $value;
        } elseif (!empty($_SESSION['id_utilisateur'])) {
            $this->id_createur = $_SESSION['id_utilisateur'];
        } else {
            $this->id_createur = -1;
        }
        return true;
    }

    /**
     * Retourne le champ date_modification
     **/
    public function getDateModification()
    {
        return Date::ukToFr($this->date_modification);
    }

    /**
     * Modifie le champ date de dernière modification par la date actuelle
     **/
    public function setDateModification($value = '')
    {
        if ($value === 'null') {
            $this->date_modification = '';
        } elseif ($value) {
            $this->date_modification = Date::frToSql($value);
        } else {
            $this->date_modification = Date::frToSql(Date::today(true));
        }
        return true;
    }

    /**
     * Modifie le champ dernier modificateur par la personne connectée
     **/
    public function setIdModificateur($value = 0)
    {
        if ($value === 'null') {
            $this->id_modificateur = '';
        } elseif ($value) {
            $this->id_modificateur = $value;
        } elseif (isset($_SESSION['id_utilisateur'])) {
            $this->id_modificateur = $_SESSION['id_utilisateur'];
        } else {
            // ce cas ne peut arriver que si l'on modifie l'objet dans une page où personne n'est connecté
            // (ex: page de première connexion)
            $this->id_modificateur = Config::paramsVariables('id_cron');
        }
        return true;
    }

    /* ******************************
    * Affichage d'infos et erreurs *
    ****************************** */

    /**
     * Réinitialise le tableau d'erreurs
     */
    public function initialiseErrors()
    {
        $this->errors = array();
    }

    /**
     * Retourne les erreurs de validation d'un objet
     * @param string $attribute : [OPT] Pour vérifier les erreurs sur un atribut en particulier
     * @return array
     */
    public function getErrors($attribute = false)
    {
        if ($attribute) {
            $tab_error = $this->errors;
            if (isset($tab_error[$attribute])) {
                return $tab_error[$attribute];
            } else {
                return false;
            }
        } else {
            return $this->errors;
        }
    }

    /*
     * Fonction utilisée par les demandes et commandes afin de trouver les différences
     * par rapport a une version modifiée
     */
    public function compareVersion()
    {
        return array();
    }


    /**
     * Met à jour le tableau des erreurs d'un objet.
     * @param {Array} $errors Le tableau d'erreurs à ajouter.
     * @return true
     */
    public function setErrors($errors)
    {
        $this->errors = array_merge($errors, $this->errors);
        return true;
    }

    /* *********************
    * Accesseurs généraux *
    ********************* */
    /**
     * Retourne la valeur d'un attribut si il existe
     * @param {String} l'attribut auquel accéder
     **/
    public function getAttribute($attribute)
    {
        return call_user_func(array($this, 'get' . \FMUP\String::getInstance()->toCamelCase($attribute)));
    }

    /**
     * Modifie la valeur d'un attribut si il existe
     * @param {String} l'attribut auquel accéder
     * @param {String} la valeur à enregistrer
     **/
    public function setAttribute($attribute, $value)
    {
        call_user_func(array($this, 'set' . \FMUP\String::getInstance()->toCamelCase($attribute)), $value);
        return true;
    }

    /**
     * Met à jour un objet (*sans* l'enregistrer dans la base de données) avec de
     * nouveaux paramètres
     * @param {Array} un tableau de nouvelles valeurs
     **/
    public function modify($params)
    {
        foreach ($params as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }

    /**
     * Retourne ou modifie la valeur d'un attribut
     * @param string $function : L'attribut auquel accéder
     * @param string $argument : [OPT] La valeur à affecter dans le cas d'une affectation
     * @return mixed|bool|null : L'argument demandée pour une lecture, VRAI si affectation réussie, null sinon
     */
    public function __call($function, $argument = array())
    {
        $attribut = \FMUP\String::getInstance()->toCamelCase(substr($function, 3));
        if (property_exists($this, $attribut)) {
            if (preg_match('#^get#i', $function)) {
                return $this->$attribut;
            }

            if (preg_match('#^set#i', $function) && count($argument)) {
                $this->$attribut = $argument[0];
                return true;
            }
        } else {
            if (preg_match('#^get#i', $function) || preg_match('#^set#i', $function)) {
                $message = "Attribut inexistant $attribut dans l'objet " . get_called_class();
            } else {
                $message = "Fonction inexistante $function dans l'objet " . get_called_class();
            }
            throw new \FMUP\Exception($message);
        }
        return null;
    }

    /**
     * Retourne l'id de l'objet
     **/
    /*public function getId()
    {
        return $this->id;
    }*/

    /**
     * Retourne le code langue de l'utilisateur
     **/
    public function getCodeLangue()
    {
        return $this->code_langue;
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
        return array();
    }

    /**
     * Donne le droit à modifier l'objet mais par une méthode différente (non directe par l'attribut)
     **/
    public function setIdNew($valeur = '')
    {
        $this->id = $valeur;
    }

    /**
     * Renvoi l'autorisation d'un champ pour l'utilisateur en cours
     *
     * @param champ à vérifier
     * @return booléen d'autorisation
     */
    public function isChampModifiable($champ)
    {
        $liste = $this->listeChampsModifiable();
        if (isset($liste[$champ])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Tableau des valeurs de l'objet typiquement $_POST
     * @return l'objet
     */
    public function setAttributesSecure($attributes)
    {
        //Sécurisation de l'id
        $identifiant = (isset($attributes['id']) && Is::id($attributes['id'])) ? $attributes['id'] : 0;
        //Récupération de l'objet en base (inutile de charger s'il n'y a pas d'ID)
        if ($identifiant) {
            $object = call_user_func(array(get_class($this), 'FindOne'), $identifiant);
        }
        if (!$identifiant || !$object) {
            $object = $this;
        }
        // récuperation des champs modifiable pour l'utilisateur courant
        $editable_fields = $object->listeChampsModifiable();
        //Si on fournit une donnée et si l'on peut la modifier
        // dans le cas de POST, il faut gérer les checkbox non cochées
        $object->initialisationVariablesAvantContructeur();
        foreach ($editable_fields as $field) {
            if (isset($attributes[$field])) {
                $object->setAttribute($field, $attributes[$field]);
            }
        }

        return $object;
    }

    public function setValeursDefautPostSave()
    {
    }

    /* *********************
    *       Logs          *
    ********************* */

    /**
     * fonction utilisée pour récupérer la liste des champs (dans l'ordre) de la table pour les requete de log
     * /!\  la clef primaire ID doit s'appeler 'id_objet_log' !!!
     * @return string
     */
    public static function listeChampsObjet()
    {
        return 'id_objet_log';
    }

    /**
     * Spécifie les champs de la table à comparer
     * Retourne un taleau vide si tous les champs de la table à comparer
     * @return Array $array
     */
    public function fieldsToCompare()
    {
        $array = array();
        return $array;
    }

    /**
     * Spécifie les champs à ne pas prendre en compte pour la comparaison
     * Tableau non vide contenant tout le temps, au moins le champ id
     * @return Array $array
     */
    public function fieldsInException()
    {
        $array = array();
        $array['id'] = 'Id';
        return $array;
    }

    /**
     * fonction de log
     * @param $type_action
     */
    public function logerChangement($type_action)
    {
        $varconnexion = Config::parametresConnexionDb();
        if (call_user_func(array(get_class($this), 'getIsLogue')) &&
            call_user_func(array(get_class($this), 'tableToLog')) &&
            $this->id
        ) {
            $default_id = (!empty($varconnexion['defaultid'])) ? $varconnexion['defaultid'] . "," : "";
            $tab = call_user_func(array(get_class($this), 'listeChampsObjet'));
            $tab = explode(', ', $tab);

            if (isset($tab[0]) && trim($tab[0]) == '') {
                unset($tab[0]);
            }

            if (isset($tab[0]) && (trim($tab[0]) == 'id')) {
                $tab[0] = 'id_objet_log';
            } elseif (isset($tab[1]) && (trim($tab[1]) == 'id')) {
                $tab[1] = 'id_objet_log';
            }
            $liste_champ = implode(', ', $tab);

            if (isset($tab[0]) && (trim($tab[0]) == 'id_objet_log')) {
                $tab[0] = 'id';
            } elseif (isset($tab[1]) && (trim($tab[1]) == 'id_objet_log')) {
                $tab[1] = 'id';
            }
            $liste_champ_valeur = implode(', ', $tab);

            $id_utilisateur = isset($_SESSION['id_utilisateur']) ? $_SESSION['id_utilisateur'] : -1;

            $SQL = 'INSERT INTO log__' . $this->getTableName() . '
                            (' . $liste_champ . '
                                , libelle_historisation
                                , contenu_log
                                , id_utilisateur_log
                                , date_action_log
                                , action_log
                            )
                        SELECT ' . $default_id . '
                                ' . $liste_champ_valeur . ',
                                \'\',
                                \'\',
                                ' . Sql::secureId($id_utilisateur) . ',
                                ' . Sql::secureDate(Date::frToSql(Date::today(true))) . ',
                                ' . Sql::secure($type_action) . '
                        FROM ' . $this->getTableName() . ' T
                        WHERE id = ' . Sql::secureId($this->id) . '
                    ';
            $db = Model::getDb();
            if ($db instanceof \FMUP\Db) {
                $db->query($SQL);
                $this->log_id = $db->lastInsertId();
            } else {
                $this->log_id = $db->execute($SQL, '', false);
            }
        }
    }

    /**
     * fonction permettant de comparer 2 tableaux de données d'un objet et sa sauvegarde en log
     * et d'enregistrer les modifications effectuées
     */
    public function comparerDifferences()
    {
        $tab_champs_comparaison = $this->fieldsToCompare();
        $tab_champs_exception = $this->fieldsInException();
        $tab_champs_base = array();
        $tab_champs_log = array();
        $tab_contenu = array();

        $champs_specifiques = false;
        if (!empty($tab_champs_comparaison)) {
            $champs_specifiques = true;
        }

        if (empty($tab_champs_exception)) {
            $tab_champs_exception["#exception#"] = "Exception";
        }


        if (call_user_func(array(get_class($this), 'getIsLogue')) && call_user_func(array(get_class($this), 'tableToLog'))) {
            // données de la table courante
            $sql = $this->getSqlLog();
            $db = Model::getDb();
            if (!$db instanceof \FMUP\Db) {
                $res = $db->requeteUneLigne($sql);
            } else {
                $res = $db->fetchRow($sql);
            }

            if ($res) {
                foreach ($res as $index => $value) {
                    if ($champs_specifiques) {
                        if (array_key_exists($index, $tab_champs_comparaison)) {
                            $tab_champs_base[$index] = $value;
                        }
                    } else {
                        if (!array_key_exists($index, $tab_champs_exception)) {
                            $tab_champs_base[$index] = $value;
                        }
                    }
                }
            }
            // données de la table de log
            $sql = $this->getSqlLog('log');
            if (!$db instanceof \FMUP\Db) {
                $res = $db->requeteUneLigne($sql);
            } else {
                $res = $db->fetchRow($sql);
            }

            if ($res) {
                foreach ($res as $index => $value) {
                    if (!array_key_exists($index, $tab_champs_exception)) {
                        if ($index == "id_objet_log") {
                            $index = "id";
                        }

                        if (array_key_exists($index, $tab_champs_base)) {
                            $tab_champs_log[$index] = $value;
                        }
                    }
                }
            }
            $tab_diff = array_diff_assoc($tab_champs_base, $tab_champs_log);
            // insertion de la différence dans la table de log
            if (count($tab_diff) > 0) {
                $libelle = "";

                foreach ($tab_diff as $index => $value) {
                    $field = ($champs_specifiques) ? $tab_champs_comparaison[$index] : $index;
                    $libelle .= "Le champ '" . $field . "' a été modifié : '"
                        . $field . "' a été remplacé par '" . $value . "'\n";

                    $tab_contenu[$index] = array(
                        "old_value" => isset($tab_champs_log[$index]) ? $tab_champs_log[$index] : null,
                        "new_value" => ($value)
                    );
                }

                $contenu = serialize($tab_contenu);
                //$contenu = json_encode($tab_contenu);

                $sql = "UPDATE log__" . $this->getTableName() . "
                        SET libelle_historisation = " . Sql::secure(($libelle)) . "
                        , contenu_log = " . Sql::secure($contenu) . "
                        WHERE id = " . Sql::secureId($this->log_id);
                $db = Model::getDb();
                if (!$db instanceof \FMUP\Db) {
                    $db->execute($sql);
                } else {
                    $db->query($sql);
                }
            }
        }
    }

    /**
     * formate les requetes de log
     */
    public function getSqlLog($from = "")
    {
        if ($from == 'log') {
            $table = 'log__' . $this->getTableName();
            $condition = " T.id = " . Sql::secureId($this->log_id);
        } else {
            $table = $this->getTableName();
            $condition = " T.id = " . Sql::secureId($this->id);
        }

        $sql = "SELECT T.*
                FROM " . $table . " T
                WHERE " . $condition;
        return $sql;
    }

    /**
     * fonction permettant de récupérer sous forme de tableau les différences
     */
    public static function returnArrayByJson($string = "")
    {
        return json_decode($string, true);
    }

    /**
     * fonction permettant de récupérer les historiques d'un objet
     */
    public function getHistoriqueSurObjet()
    {
        $sql = "SELECT *
                FROM log__" . $this->getTableName() . "
                WHERE id_objet_log = " . Sql::secureId($this->id) . "
                ORDER BY id";
        $db = \Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $res = $db->requete($sql);
        } else {
            $res = $db->fetchAll($sql);
        }
        return $res;
    }

    /**
     * fonction permettant de récupérer les historiques de libelle d'un objet
     */
    public function getHistoriqueSurObjetDiffLibelle()
    {
        $sql = "SELECT libelle_historisation, id_utilisateur_log, date_action_log, action_log
                FROM log__" . $this->getTableName() . "
                WHERE id_objet_log = " . Sql::secureId($this->id) . "
                ORDER BY id";
        $db = \Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $res = $db->requete($sql);
        } else {
            $res = $db->fetchAll($sql);
        }
        return $res;
    }

    /**
     * fonction permettant de récupérer les historiques tableau d'un objet
     */
    public function getHistoriqueSurObjetDiffArray()
    {
        $array = array();
        $sql = "SELECT id, contenu_log, id_utilisateur_log, date_action_log, action_log
                FROM log__" . $this->getTableName() . "
                WHERE id_objet_log = " . Sql::secureId($this->id) . "
                ORDER BY id";
        $db = \Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $res = $db->requete($sql);
        } else {
            $res = $db->fetchAll($sql);
        }

        foreach ($res as $rs) {
            $array[$rs["id"]] = array(
                "id_utilisateur_log" => $rs["id_utilisateur_log"]
            , "date_action_log" => $rs["date_action_log"]
            , "action_log" => $rs["action_log"]
            , "contenu_log" => unserialize($rs["contenu_log"])
            );
        }
        return $array;
    }

    /* ************
    * Validation *
    ************ */
    /**
     * L'objet est-il enregistrable en base de données
     * @return bool
     */
    abstract public function validate();

    /**
     * L'objet est-il effaçable
     * @return bool
     */
    abstract public function canBeDeleted();

    /**
     * Détermine si notre objet est unique par rapport aux attributs donnés
     * @param mixed $attribut : Attribut (ou liste d'attribut, dans ce cas tableau) à comparer
     * @param array $where : [OPT] Clauses supplémentaires à prendre en compte pour notre recherche
     * @return bool : VRAI si aucun doublon est trouvé
     */
    public function isUniqueAttribute($attribut, $where = array())
    {
        if (is_array($attribut)) {
            foreach ($attribut as $current_attribut) {
                $where[$current_attribut] = 'IFnull(' . $current_attribut . ', 0) = '
                    . 'IFnull(' . sql::Secure($this->$current_attribut) . ', 0)';
            }
        } else {
            $where[$attribut] = 'IFnull(' . $attribut . ', 0) = IFnull(' . sql::Secure($this->$attribut) . ', 0)';
        }
        $where['id'] = "IFnull(id, 0) <> IFnull(" . sql::secureId($this->id) . ", 0)";
        $doublon = call_user_func(array(get_class($this), 'FindFirst'), $where);

        if ($doublon) {
            return false;
        } else {
            return true;
        }
    }
}
