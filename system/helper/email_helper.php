<?php

class EmailHelper
{
    /**
     * Envoi un mail avec ou sans pièces jointes
     * @param $identifiant id du template à utiliser
     * @param $send_to Destinataire
     * @param $tokens Tokens à remplacer dans le message
     * @param $files tableau de fichier sous la forme path, name et mime
     * @param $handle ressource du fichier de log
     * @param $email_cache copie cache
     * @param $params paramètres utilisés pour les accusés de réceptions des mails (stockés dans les emails_log)
     * @return retour de la fonction mail
     */
    public static function sendEmail($identifiant, $send_to, $tokens = array(), $files = array(), $handle = false, $email_cache = "", $params = array())
    {
        $my_mail = new PHPMailer(true);

        if (preg_match('|^(?:[^<]*<)?([^>]*)(?:>)?$|i', $send_to, $matches) || !Config::isEnvoiMailPossible($identifiant)) {
            $adresse_mail = $matches[1];
            $email = Email::findOne($identifiant);

            // on va vérifier que tous les emails ont bien un bon format
            // séparateur d'email, le ';'
            $no_problem = true;
            $liste_mail = explode(";", $adresse_mail);
            foreach ($liste_mail as $e) {
                if (!Is::courriel($e) && Config::isEnvoiMailPossible($identifiant)) {
                    if ($handle) {
                        FileHelper::writeLogLine($handle, 'problème adresse mail : "'.$e.'" - '.$send_to);
                    } else {
                        throw new Error('adresse email incorrecte : "'.$e.'"');
                    }
                    $no_problem = true;
                }
            }

            if ($no_problem && $email) {
                try {
                    //TODO : en attendant de faire mieux avec le nom et prénom de la personne
                    $to = $adresse_mail;
                    $message = emailHelper::remplaceToken($email->getMessage(), $tokens);

                    if (Config::smtpServeur() != 'localhost') {
                        $my_mail->IsSMTP();
                    }
                    $my_mail->CharSet = "UTF-8";
                    $my_mail->SMTPAuth   = Config::smtpAuthentification();
                    $my_mail->SMTPSecure = Config::smtpSecure();

                    $my_mail->Host   = Config::smtpServeur();
                    $my_mail->Port   = Config::smtpPort();

                    if (Config::smtpAuthentification()) {
                        $my_mail->Username   = Config::smtpUsername();     // Gmail identifiant
                        $my_mail->Password   = Config::smtpPassword();		// Gmail mot de passe
                    }


                    $my_mail->IsHTML(true);

                    $my_mail->SetFrom(Config::mailRobot(), Config::mailRobotName());
                    $my_mail = self::addReplyTo($my_mail, Config::mailReply(), Config::mailReplyName());

                    $my_mail = self::addBCC($my_mail, $email_cache);


                    $adress_caches = '';
                    $my_mail = self::addBCC($my_mail, $adress_caches);


                    $objet = emailHelper::remplaceToken($email->getObjet(), $tokens);
                    if (!Config::isEnvoiMailPossible($identifiant)) {
                        $objet .= ' ## '.$send_to;
                    }
                    if (Config::paramsVariables('version') == 'dev') {
                        $objet  = ' ** DEV ** '.$objet;
                    }
                    $my_mail->Subject = $objet;

                    foreach ($files as $files_infos) {
                        $nom_fichier = (isset($files_infos['name']))? $files_infos['name'] : '';
                        if(file_exists($files_infos['path'])){
                            $my_mail->AddAttachment($files_infos['path'], $nom_fichier);
                        }
                    }

                    $adress = emailHelper::rempAdresse($send_to, $identifiant);
                    $log_adress = $adress;
                    $my_mail = self::addAddress($my_mail, $adress);

                    /* Log des envois de mail */
                    $log_mail = new EmailLog(array('id_email' => $identifiant,
                                    'objet' => $objet,
                                    'message' => $message,
                                    'destinataire' => $log_adress,
                                    'destinataire_cache' => $adress_caches));
                    if (!$log_mail->save()) {
                        if ($handle) {
                            FileHelper::writeLogLine($handle, "Problème rencontré dans l'enregistrement email_log : ".print_r($log_mail));
                        }
                    } elseif ($params) {
                        // gestion des accusés de reception
                        $code_unique = md5($log_mail->getId());
                        $log_mail->setCodeAccuseReception($code_unique);
                        $log_mail->setParametres(serialize($params));
                        $log_mail->save();

                        $message = str_replace('%code_unique%', $code_unique, $message);
                    }

                    $my_mail->MsgHTML($message);
                    return $my_mail->Send();

                } catch (phpmailerException $e) {
                    if ($handle) {
                        FileHelper::writeLogLine($handle, $e->errorMessage());
                    }
                    //emailHelper::sendEmailErreur($identifiant, $e->getMessage(), $log_adress, $objet, $message);
                } catch (Exception $e) {
                    if ($handle) {
                        FileHelper::writeLogLine($handle, $e->getMessage());
                    }
                    //emailHelper::sendEmailErreur($identifiant, $e->getMessage(), $log_adress, $objet, $message);
                }
            } else {
                throw new Error(Error::emailTemplateAbsent($identifiant));
            }
        } else {
            if ($handle) FileHelper::writeLogLine($handle, 'adresse email non reconnue : '.$send_to);
        }
    }

    public static function sendEmailErreur($id_email, $erreur, $destinataire_origine, $objet_origine = "", $message_origine = "")
    {
        $erreur_mail = new PHPMailer(true);
        $erreur_mail->IsHTML(true);
        $erreur_mail->CharSet = "UTF-8";

        $erreur_mail->SetFrom(Config::mailRobot(), Config::mailRobotName());
        $erreur_mail = self::addReplyTo($erreur_mail, Config::mailReply(), Config::mailReplyName());

        $objet= "";
        if (Config::paramsVariables('version') != 'prod') {
            $objet .= "** " . strtoupper(Config::paramsVariables('version')) . " ** ";
        }
        $objet .= "Erreur rencontrée lors d'un envoi de mail";
        $erreur_mail->Subject = $objet;

        $message = "L'envoi de l'email suivant a échoué
                    <br>
                    <br>
                    <hr>
                    <br>
                    <b>Erreur rencontrée : </b>".$erreur."
                    <br>
                    <b>Objet : </b>".$objet_origine."
                    <br><br>
                    <b>Destinataire(s) : </b><br>";
        $adresses = explode(';', $destinataire_origine);
        foreach ($adresses as $adress) {
            $message .= "&nbsp;&nbsp;&nbsp; - ".trim($adress)."<br>";
        }

        $message .= "<br><hr><br>
                    <b>Message : </b>
                    <br><br>
                    ";
        $message .= $message_origine;


        $erreur_mail->MsgHTML($message);

        $erreur_mail = self::addAddress($erreur_mail, Config::mailSupport());

        $erreur_mail->Send();
    }

    /**
     * Remplace les tokens dans un message
     * @param $message texte à parser
     * @param $tokens tableau associatif des tokens à remplacer
     * @return message traité
     */

    public static function remplaceToken($message = '', $tokens = array())
    {
        $search = array_keys($tokens);
        $replace = array_values($tokens);
        $search = array_map(create_function('$o', 'return "{".$o."}";'), $search);
        return str_replace($search, $replace, $message);
    }
    /**
     * Remplace l'adresse mail en debug
     */
    public static function rempAdresse($adresse, $id_type_mail = 0)
    {
        if (!Config::isEnvoiMailPossible($id_type_mail)) {
            $adresse = Config::mailEnvoieTest();
        }

        if (Config::paramsVariables('version') != 'dev') {
            /////
        }

        return $adresse;
    }

    /*
     * Envoi d'un email sans Log, sans fichier attaché et sans copie
     * A UTILISER pour l'admin uniquement
     */
    public static function sendEmailSimple($objet, $to, $message)
    {
        $my_mail = new PHPMailer(true);
        $my_mail->IsHTML(true);
        $my_mail->CharSet = "UTF-8";
        $my_mail->SetFrom(Config::mailRobot(), Config::mailRobotName());

        $my_mail = self::addReplyTo($my_mail, Config::mailReply(), Config::mailReplyName());

        if(Config::paramsVariables('version') == 'dev')
            $objet  = ' ** DEV ** '.$objet;

        $my_mail->Subject = $objet;
        $my_mail->MsgHTML($message);

        $log_adress = $to;
        $my_mail = self::addAddress($my_mail, $to);

        // on met CASTELIS en copie (pour les tests)
        $my_mail = self::addBCC($my_mail, Config::mailSupport());

        return $my_mail->Send();
    }

    /*
     * fonction qui retourne l'email a utiliser si l'on est en PROD ou en DEV
     * pour ne pas envoyer les mails de tests aux vrai gens
     *
     * pâr défaut cette fonction est utilisée pour la synchronsation
     * donc si elle est utiliser ailleurs, il faut préciser l'email admin d'envoi...
     */
    public static function getEmailAUtiliser($type_destinataire = 'admin', $email = '', $email_admin = '')
    {
        if ($email_admin == '') {
            $param       = Parametre::findOne(Constantes::getIdParametreEmailPourSynchro());
            $email_admin = $param->getValeur();
        }

        if ((Config::paramsVariables('version') == 'prod') || (Config::paramsVariables('version') == 'CRON')) {
            if ($type_destinataire == 'admin') {
                $email = $email_admin;
            } elseif (!is::courriel($email)) {
                $email = $email_admin;
            }
        } else {
            $email = Config::mailSupport();
        }
        return $email;
    }

/****************************************************************************************
 *  fonctions à utiliser pour décoder les adresses mails contenants des ';' ou des ','  *
 ****************************************************************************************/

    public static function addReplyTo($my_mail, $adresses = '', $nom_adresse = '')
    {
        if ($adresses='') 		$adresses=Config::mailReply();
        if ($nom_adresse='') 	$nom_adresse=Config::mailReplyName()	;
        $tmp = self::explodeListEmails($adresses);
        foreach ($tmp as $adress) {
            if ($adress != "" && Is::courriel($adress)) {
                $my_mail->AddReplyTo(trim($adress), $nom_adresse);
            }
        }
        return $my_mail;
    }

    public static function addAddress($my_mail, $adresses, $nom_adresse = '')
    {
        $tmp = self::explodeListEmails($adresses);
        foreach ($tmp as $adress) {
            if ($adress != "" && Is::courriel($adress)) {
                $my_mail->AddAddress(trim($adress));
            }
        }
        return $my_mail;
    }

    public static function addBCC($my_mail, $adresses, $nom_adresse = '')
    {
        $tmp = self::explodeListEmails($adresses);
        foreach ($tmp as $adress) {
            if ($adress != "" && Is::courriel($adress)) {
                $my_mail->AddBCC(trim($adress));
            }
        }
        return $my_mail;
    }
    /*
     * transforme une liste d'email de String en tableau
     * @param string  ex: shuet@castelis.com;jha@castelis.com
     * @return table  ex: array('shuet@castelis.com', 'jha@castelis.com')
     */
    public static function explodeListEmails($adresses = '')
    {
        if (strpos($adresses, ';')===false) {
            return explode(',', $adresses);
        } else {
            return explode(';', $adresses);
        }
    }
}
