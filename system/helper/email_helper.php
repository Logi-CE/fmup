<?php

/**
 * Classe permettant l'envoi d'email grâce à PHPMailer
 * @version 1.0
 */
class EmailHelper
{
    /**
     * @param PHPMailer $my_mail
     * @return PHPMailer
     */
    public static function parametrerHeaders(PHPMailer $my_mail)
    {
        if (Config::paramsVariables('smtp_serveur') != 'localhost') {
            $my_mail->IsSMTP();
        }
        $my_mail->IsHTML(true);
        $my_mail->CharSet = "UTF-8";
        $my_mail->SMTPAuth = Config::paramsVariables('smtp_authentification');
        $my_mail->SMTPSecure = Config::paramsVariables('smtp_secure');

        $my_mail->Host = Config::paramsVariables('smtp_serveur');
        $my_mail->Port = Config::paramsVariables('smtp_port');

        if (Config::paramsVariables('smtp_authentification')) {
            $my_mail->Username = Config::paramsVariables('smtp_username'); // Gmail identifiant
            $my_mail->Password = Config::paramsVariables('smtp_password'); // Gmail mot de passe
        }

        return $my_mail;
    }

    /**
     * Remplace les tokens dans un message
     * @param $message texte à parser
     * @param $tokens tableau associatif des tokens à remplacer
     * @return message traité
     * @uses self::tokenReplaceMap
     */
    public static function remplaceToken($message = '', $tokens = array())
    {
        $search = array_keys($tokens);
        $replace = array_values($tokens);
        $search = array_map(array('\EmailHelper', 'tokenReplaceMap'), $search);
        return str_replace($search, $replace, $message);
    }

    private static function tokenReplaceMap($o)
    {
        return "{" . $o . "}";
    }
}
