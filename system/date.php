<?php
/**
 * Cette classe contient des fonctions de formatage ou de calcul de date
 */
class Date
{
    /**
     * Transforme une date FR jj/mm/aaaa en date UK
     * @param string $ma_date : La date en format français (avec ou sans les heures) à convertir
     * @return string : La date UK si la date passée en paramètre est FR, si c'est une date UK, elle est renvoyée, sinon FAUX
     */
    public static function frToUk($ma_date)
    {
        // Si la date est correcte, on essaie de la convertir
        if (Is::dateTime($ma_date)) {
            if (stripos($ma_date, ' ') === false) {
                $heure = '';
                list($jour, $mois, $annee) = preg_split('|[/.-]|', $ma_date);
            } else {
                list($date, $heure) = explode(' ', $ma_date);
                list($jour, $mois, $annee) = preg_split('|[/.-]|', $date);
                $heure = ' '.$heure;
            }
            // Si l'année est en deux caractères, on la complète
            if (strlen($annee) == 2) $annee = '20'.$annee;
            $retour = $annee.'-'.$mois.'-'.$jour.$heure;
            
        // Si la date est déjà convertie, on la renvoie
        } elseif (Is::dateTimeUk($ma_date)) {
            $retour = $ma_date;
            
        // Sinon on renvoie FAUX
        } else {
            $retour = false;
        }
        return $retour;
    }

	/**
     * Transforme une date UK en date FR jj/mm/aaaa
     * @param string $ma_date : La date en format UK (avec ou sans les heures) à convertir
     * @return string : La date FR si la date passée en paramètre est UK, si c'est une date FR, elle est renvoyée, sinon FAUX
     */
    public static function ukToFr($ma_date)
    {
        // Si la date est correcte, on essaie de la convertir
        if (Is::dateTimeUk($ma_date)) {
            if (stripos($ma_date, ' ') === false) {
                $heure = '';
                list($annee, $mois, $jour) = preg_split('|[/.-]|', $ma_date);
            } else {
                list($date, $heure) = explode(' ', $ma_date);
                list($annee, $mois, $jour) = preg_split('|[/.-]|', $date);
                $heure = ' '.$heure;
            }
            // Si l'année est en deux caractères, on la complète
            if (strlen($annee) == 2) $annee = '20'.$annee;
            $retour = $jour.'/'.$mois.'/'.$annee.$heure;
            
        // Si la date est déjà convertie, on la renvoit
        } elseif (Is::dateTime($ma_date)) {
            $retour = $ma_date;
            
        // Sinon on renvoit FAUX
        } else {
            $retour = false;
        }
        return $retour;
    }

    /**
     * Transforme une date francaise jj/mm/aaaa en date sql pour la BDD
     * @param string $ma_date : La date en format français (avec ou sans les heures) à convertir
     * @return string : La date UK si la date passée en paramètre est FR, si c'est une date UK, elle est renvoyée, sinon FAUX
     */
    public static function frToSql($ma_date)
    {
        // Si la date est correcte, on essaie de la convertir
        if (Is::dateTime($ma_date)) {
            $temp = Config::parametresConnexionDb();
            if ($temp['driver'] == 'mysql') {
                $retour = self::frToUk($ma_date);
            } elseif ($temp['driver'] == 'mssql') {
                $retour = $ma_date;
            }
        // Si la date est déjà convertie, on la renvoie
        } elseif (Is::dateTimeUk($ma_date)) {
            $retour = $ma_date;
            
        // Sinon on renvoie FAUX
        } else {
            $retour = false;
        }
        return $retour;
    }

    /**
     * retourne la date  d'une dateTime
     * @param string $ma_date : la date en format fr OU uk
     */
    public static function getDateUniquement($ma_date)
    {
        $resultat = explode(' ', $ma_date);
        $date = '';
        if (count($resultat)>0) {
            $date = $resultat[0];
        }
        return $date;
    }

    /**
     * retourne l'heure d'une dateTime
     * @param string $ma_date : la date en format fr OU uk
     */
    public static function getHeure($ma_date)
    {
        $resultat = explode(' ', $ma_date);
        $heure = '';
        if (count($resultat)>1) {
            $heure = $resultat[1];
        }
        return $heure;
    }

    /**
     * Fonction retournant la durée représentée par un intervalle (en minutes)
     * @param string $heure_debut : L'heure de début de l'intervalle au format xxhxx
     * @param string $heure_fin : L'heure de fin de l'intervalle au format xxhxx
     * @return int : La durée de l'intervalle en minutes
     */
    public static function getDureeIntervalle($heure_debut, $heure_fin)
    {
        if ($heure_debut && $heure_fin) {
            $debut = Date::getTabHeure($heure_debut);
            $fin   = Date::getTabHeure($heure_fin);

            $diff_heures = $fin['heure'] - $debut['heure'];
            $diff_minutes = ($fin['minute'] - $debut['minute'] + 60) % 60;
            if ($fin['minute'] < $debut['minute']) {
                $diff_heures--;
            }
            return $diff_heures * 60 + $diff_minutes;
        }
        return null;
    }

    /**
     * Ajout une durée (en minutes) à une heure donnée
     * @param {String} $mon_heure : l'heure à laquelle ajouter la durée (au format xxhxx)
     * @param {Int} $duree : la durée à ajouter (en minutes)
     * @return la nouvelle heure au format xxhxx
     */
    public static function addMinutesHeures($mon_heure, $duree)
    {
        $tab_heure = Date::getTabHeure($mon_heure);
        $heure_modifiee = date('H:i', mktime($tab_heure['heure'], $tab_heure['minute'] + $duree, 0, 1, 1, 2012));
        return str_replace(':', 'h', $heure_modifiee);
    }
    
    /**
     * Ajoute du temps à une date
     * @param string $ma_date : Date au format français
     * @param string $type_ajout : Type d'ajout entre "minute", "heure", "jour", "mois", "annee"
     * @param int $nombre : Nombre du type demandé à ajouter - peut être négatif
     */
    public static function addTime ($ma_date, $type_ajout, $nombre) {
        $mots_anglais = array('minute' => 'minutes', 'heure' => 'hours', 'jour' => 'days', 'mois' => 'months', 'annee' => 'years');
        if (Is::dateTime($ma_date) && Is::integer($nombre) && isset($mots_anglais[$type_ajout])) {
            $date_uk = Date::frToUk($ma_date);
            return date('d/m/Y H:i:s', (strtotime($date_uk.' '.$nombre.' '.$mots_anglais[$type_ajout])));
        } else {
            return false;
        }
    }

    /**
     * Transforme une date numérique en date complète
     * @param string $ma_date : [OPT] La date à formater en français, par défaut la date du jour
     * @param string $locale : [OPT] La langue de formatage, par défaut le français
     * @return string : La date sous la forme "jour de la semaine + jour du mois + mois + annee"
     */
    public static function formatDateComplete($ma_date = false, $locale = 'fra_fra')
    {
        $timestamp = ($ma_date) ? strtotime(Date::frToUk($ma_date)) : time();
        setlocale(LC_TIME, $locale);
        return ucwords(utf8_encode(strftime('%A %d %B %Y', $timestamp)));
    }

    /**
     * Compare 2 dates au format français (JJ/MM/AAAA) avec ou sans heure
     * @param string $date1 : Première date
     * @param string $date2 : Seconde date
     * @return int : 1 si date1 > date2, 0 si egale, -1 sinon
     */
    public static function compareFr($date1, $date2)
    {
        if (Is::dateTime($date1) && Is::dateTime($date2)) {
            $date1 = new DateTime(self::frToUk($date1));
            $date2 = new DateTime(self::frToUk($date2));

            if ($date1->getTimestamp() > $date2->getTimestamp()) {
                return 1;
            } elseif ($date1->getTimestamp() == $date2->getTimestamp()) {
                return 0;
            }
            return -1;
        }
        return false;
    }

    /**
     * Evalue si deux périodes se chevauchent
     * @param string $date_debut1 : La date de début de la première période
     * @param string $date_fin1 : La date de fin de la première période
     * @param string $date_debut2 : La date de début de la seconde période
     * @param string $date_fin2 : La date de fin de la seconde période
     * @return bool : VRAI si les deux périodes se chevauchent
     */
    public static function chevauchementPeriodes($date_debut1, $date_fin1, $date_debut2, $date_fin2)
    {
        if ((self::compareFr($date_debut1, $date_debut2) >= 0 && self::compareFr($date_debut1, $date_fin2) <= 0)
            || (self::compareFr($date_fin1, $date_debut2) >= 0 && self::compareFr($date_fin1, $date_fin2) <= 0)
            || (self::compareFr($date_debut1, $date_debut2) >= 0 && self::compareFr($date_fin1, $date_fin2) <= 0)
            || (self::compareFr($date_debut1, $date_debut2) <= 0 && self::compareFr($date_fin1, $date_fin2) >= 0)) {
                return true;
        }
        return false;
    }

    /**
     * Evalue si deux tranches horaires sur la même journée (sous la forme xxhxx) se chevauchent
     * @param string heure_debut1 : L'heure de début de la première période
     * @param string heure_fin1 : L'heure de fin de la première période
     * @param string heure_debut2 : L'heure de début de la seconde période
     * @param string heure_fin2 : L'heure de fin de la seconde période
     * @return bool : VRAI si les deux périodes se chevauchent
     */
    public static function chevauchementTranchesHoraires($heure_debut1, $heure_fin1, $heure_debut2, $heure_fin2)
    {
        if (($heure_debut1 >= $heure_debut2 && $heure_debut1 <= $heure_fin2)
            || ($heure_fin1 >= $heure_debut2 && $heure_fin1 <= $heure_fin2)
            || ($heure_debut1 >= $heure_debut2 && $heure_fin1 <= $heure_fin2)
            || ($heure_debut1 <= $heure_debut2 && $heure_fin1 >= $heure_fin2)) {
                return true;
        }
        return false;
    }

    /**
     * Retourne la date du jour
     * @param bool $heure : [OPT] Affiche la date avec l'heure, par défaut non
     * @param bool $format : [OPT] Affiche la date au format français "FR" ou américain "US", par défaut "FR"
     * @return string : La date du jour
     */
    public static function today($heure = false, $format = 'FR')
    {
        if ($format == 'FR') {
            $retour = date('d/m/Y');
        } else {
            $retour = date('Y-m-d');
        }
        if ($heure) {
            $retour .= ' '.self::now();
        }
        return $retour;
    }
    
	/**
     *  Retourne l'heure courante
     *  @return string : L'heure courante
     */
    public static function now ()
    {
        return date("H:i:s");
    }

    /**
     * Retourne l'année en cours
     * @return année en cours
     */
    public static function getAnneeEnCours()
    {
        return date('Y');
    }

    /**
     * Retourne le mois de la date du jour
     * @return mois du jour
     */
    public static function getMoisEnCours()
    {
        return date('m');
    }

    /**
     * Fonction permettant de compter le nombre de jours entre deux dates
     * @param string $date_debut : La date de début
     * @param string $date_fin : La date de fin
     * @param string $jours_ouvres : [OPT] Indique s'il faut compter les jours ouvrés seulement, par défaut non
     * @return : Le nombre de jours entre ces deux dates + 1 (dernier jour inclu), nombre toujours positif
     */
    public static function getNbJours($date_debut, $date_fin, $jours_ouvres = false)
    {
        $nb_jours = false;
        if (Is::dateTime($date_debut) && Is::dateTime($date_fin)) {
            $nb_jours = 1;
            $date_en_cours = new DateTime(self::frToUk($date_debut));
            $date_fin = new DateTime(self::frToUk($date_fin));
            if ($date_en_cours > $date_fin) {
                $date_en_cours = $date_fin;
                $date_fin = new DateTime(self::frToUk($date_debut));
            }
            
            while ($date_en_cours < $date_fin) {
                if (!$jours_ouvres || self::estOuvre($date_en_cours->format('d/m/Y'))) {
                    $nb_jours++;
                }
                $date_en_cours->modify('+1 days');
            }
        }
        
        return $nb_jours;
    }
    
    /**
     * Détermine si un jour est ouvré ou non, en comptant les jours fériés et les weekends
     * @param string $date : Date au format français
     * @return bool VRAI si le jour est ouvré, sinon FAUX
     */
    public static function estOuvre ($date)
    {
        $retour = true;
        $date = new DateTime(self::frToUk($date));
        $jours_feries = self::getJoursFeries($date->format('Y'));
        if (isset($jours_feries[$date->format('n').'_'.$date->format('j')]) || $date->format('N') == 6 || $date->format('N') == 7) {
            $retour = false;
        }
        return $retour;
    }

    /**
     * Ajoute un certain nombre de jours ouvrés à une date française
     * @param string $date : Date de départ au format français
     * @param int $nb_jours : Nombre de jour à ajouter potentiellement négatif
     * @return string : Date au format français
     */
    public static function ajouterJoursOuvres ($date, $nb_jours)
    {
        $modificateur = 1;
        if ($nb_jours < 0) {
            $nb_jours = abs($nb_jours);
            $modificateur = -1;
        }
        
        $date = new DateTime(self::frToUk($date));
        while ($nb_jours > 0 || !self::estOuvre($date->format('d/m/Y'))) {
            if (self::estOuvre($date->format('d/m/Y'))) {
                $nb_jours--;
            }
            $date->modify($modificateur.' days');
        }
        
        return $date->format('d/m/Y');
    }
    
	/**
     * retourne un tableau avec les minutes et les heures d'une heure
     * ex :  9h30 -->  heure:9  et minute:30
     * 		soit array('heure'=>9, 'minute'=>30)
     */
    public static function getTabHeure($heure)
    {
        $tmp = array('heure'=>0, 'minute'=>0);
        if (Is::alphaNumerique($heure) && preg_match('#^([0-9]{2})h([0-9]{2})$#', $heure, $tab)) {
            $tmp = array('heure' => $tab[1], 'minute' => $tab[2]);
        }
        return $tmp;
    }

    public static function getTableauLibelleMoisPourSelect($lang = "")
    {
        $tmp = array();

        $tmp[] = array('valeur'=>'', 'texte'=>'* Tous *');

        foreach (self::getTableauLibelleMois($lang) as $id => $mois) {
            $tmp[] = array('valeur'=>$id, 'texte'=>$mois);
        }

        return $tmp;
    }

    /**
     * retourne le libellé du jour (en français par défaut)
     * lundi = 1  et dimanche = 7
     */
    public static function getLibelleJour($num_jour, $lang = 'FR')
    {
        $libelle = self::getTableauLibelleJours($lang);

        if(isset($libelle[$num_jour]))
            return $libelle[$num_jour];

        return '';

    }
    public static function getLibelleMois($num_mois, $lang = 'FR')
    {
        $libelle = self::getTableauLibelleMois($lang);

        if(isset($libelle[$num_mois]))
            return $libelle[$num_mois];

        return '';

    }

    public static function getTableauLibelleJours($langue = 'FR')
    {
        $tableau = array(
            'FR' => array(
                    '1' => 'Lundi',
                    '2' => 'Mardi',
                    '3' => 'Mercredi',
                    '4' => 'Jeudi',
                    '5' => 'Vendredi',
                    '6' => 'Samedi',
                    '7' => 'Dimanche',
                ),
            'EN' => array(
                    '1' => 'Monday',
                    '2' => 'Tuesday',
                    '3' => 'Wednesday',
                    '4' => 'Thursday',
                    '5' => 'Friday',
                    '6' => 'Saturday',
                    '7' => 'Sunday',
                ),
            'ES' => array(
                    '1' => 'Lunes',
                    '2' => 'Martes',
                    '3' => 'Miercoles',
                    '4' => 'Jueves',
                    '5' => 'Viernes',
                    '6' => 'Sabado',
                    '7' => 'Domingo',
                )
            );

        if(isset($tableau[$langue]))
            return $tableau[$langue];

        return array();
    }

    public static function getTableauLibelleMois($langue = 'FR')
    {
        if ($langue=='') {
            $langue = 'FR';
        }

        $tableau = array(
            'FR' => array(
                    '1'  => 'Janvier',
                    '2'  => 'Février',
                    '3'  => 'Mars',
                    '4'  => 'Avril',
                    '5'  => 'Mai',
                    '6'  => 'Juin',
                    '7'  => 'Juillet',
                    '8'  => 'Aout',
                    '9'  => 'Septembre',
                    '10' => 'Octobre',
                    '11' => 'Novembre',
                    '12' => 'Décembre'
                ),
            'EN' => array(
                    '1'  => 'January',
                    '2'  => 'February',
                    '3'  => 'March',
                    '4'  => 'April',
                    '5'  => 'May',
                    '6'  => 'June',
                    '7'  => 'July',
                    '8'  => 'August',
                    '9'  => 'September',
                    '10' => 'October',
                    '11' => 'November',
                    '12' => 'December'
                ),
            'ES' => array(
                    '1'  => 'Enero',
                    '2'  => 'Febrero',
                    '3'  => 'Marzo',
                    '4'  => 'Abril',
                    '5'  => 'Mayo',
                    '6'  => 'Junio',
                    '7'  => 'Julio',
                    '8'  => 'Agosto',
                    '9'  => 'Septiembre',
                    '10' => 'Octubre',
                    '11' => 'Noviembre',
                    '12' => 'Diciembre'
                )
            );

        if(isset($tableau[$langue]))
            return $tableau[$langue];

        return array();
    }
    
	/**
     * Retourne tous les jours fériés de l'année donnée
     * @param int $annee : L'année demandée
     * @return array : Un tableau sous la forme 'mois_jour' => nom du jour férié
     */
    public static function getJoursFeries ($annee)
    {
        $timestamp_paques = easter_date($annee);
        
        // Mois_Jour (pour les ordonner)
        return array(
              "1_1" => "Jour de l'an"
            , "5_1" => "Fête du travail"
            , "5_8" => "Victoire 1945"
            , "7_14" => "Fête nationale"
            , "8_15" => "Assomption"
            , "11_1" => "Toussaint"
            , "11_11" => "Armistice 1918"
            , "12_25" => "Nöel"
            , date('n_j', $timestamp_paques + (86400 * 1)) => 'Lundi de Pâques'
            , date('n_j', $timestamp_paques + (86400 * 39)) => 'Ascension'
            , date('n_j', $timestamp_paques + (86400 * 50)) => 'Lundi de Pentecôte'
        );
    }

    /**
     * Retourne la date du dernier jour ouvré
     * @return date
     * @todo : Utiliser Date::estOuvre() dans une boucle, et tant qu'à faire mettre un paramètre pour choisir la date
     */
    public static function getDateDernierJourOuvre ()
    {
        $date = new DateTime(Date::today(false, 'UK'));
        $hier = $date->format('Y-m-d');
        $jour = $date->format('l');
        //Si nous sommes lundi nous vérifions la clôture du samedi
        if($jour == "Monday"){
            $date->modify('-2 days');
            $hier = $date->format('Y-m-d');
        }else{
            $date->modify('-1 days');
            $hier = $date->format('Y-m-d');
        }

        return $hier;
    }

    public static function getDateVeille($date)
    {
        $dateTimestamp = strtotime(Date::frToUk($date));
        $date          = date('Y-m-d', strtotime('-1 days', $dateTimestamp));
        //gestion du cas du dimanche
        if(date('l', strtotime($date)) == 'Sunday'){
            $dateTimestamp = strtotime(Date::frToUk($date));
            $date          = date('Y-m-d', strtotime('-1 days', $dateTimestamp));
        }
        return $date;
    }

    /**
     * Retourne la date d'anniversaire
     */
    public static function isDateAnniversaire($ma_date)
    {   
        $return = false;

        if($ma_date == Date::today()){
            $return = true;
        }
        return $return;
    }

	/**
     * @deprecated utiliser getDateUniquement
     */
    public static function getDateWithoutHeure($ma_date)
    {
        return self::getDateUniquement($ma_date);
    }
    
	/**
     * @deprecated : Utiliser addTime
     */
    public static function addMinutesDate($ma_date, $nb_minutes)
    {
        return self::addTime($ma_date, 'minute', $nb_minutes);
    }

    /**
     * @deprecated : Utiliser addTime
     */
    public static function addDay($ma_date, $nb_jours)
    {
        return self::addTime($ma_date, 'jour', $nb_jours);
    }
    
    /**
     * @deprecated : Utiliser today
     */
    public static function todayWithHeure()
    {
        return self::today(true);
    }
    
    /**
     * @deprecated : Utiliser today
     */
    public static function todayWithHeureUk()
    {
        return self::today(true, 'US');
    }
    
    /**
     * @deprecated : Utiliser today
     */
    public static function todayFr ($heure = false)
    {
        self::today();
    }
    
    /**
     * @deprecated : Utiliser today
     */
    public static function todaySql ($heure = false)
    {
        self::today(false, 'US');
    }
    
	/**
     * @deprecated : Utiliser compareFr
     */
    public static function compareFrWithHeure($date1, $date2)
    {
        return self::compareFr($date1, $date2);
    }

	/**
     * @deprecated : Utiliser ukToFr
     */
    public static function ukToFrWithHeure($ma_date)
    {
        return Date::ukToFr($ma_date);
    }
    
	/**
     * @deprecated : Utiliser ukToFr
     */
    public static function sqlToFr($ma_date)
    {
        return self::ukToFr($ma_date);
    }	
}
