<?php
class Langue 
{

    static function getTraductionLive($keylist, $code_language="") 
    {        
        $tabretour = array();
        if (trim($keylist) != "") {
            $code_language_todo = $GLOBALS["codelanguagedefaut"];
            if ($code_language != "") {
                $code_language_todo = $code_language;
            }
            
            $sql = "SELECT LT.*
                        FROM translations LT 
                            LEFT JOIN languages L ON L.id = LT.id_language
                        WHERE LT.translation_key IN (".$keylist.")
                        AND L.code = '".$code_language_todo."'";
            $translation = Model::getDb()->requete($sql);
            foreach ($translation as $trad) {
                $tabretour[$trad["translation_key"]] = $trad["translation"];
            }
        }		
        return $tabretour;
    }

    static function chargeFile($file, $param_code_language=0) 
    {

        $code_language = $GLOBALS["codelanguagedefaut"];
        if ($param_code_language > 0) {
            $code_language = $param_code_language;
        }
        
        $chemin = BASE_PATH."/data/translation/".trim($code_language)."/".$file;
        
        if (is_file($chemin)) {		
            require_once ($chemin);
        } else {
            $chemin = BASE_PATH."/data/translation/".trim($GLOBALS["codelanguagedefaut"])."/".$file;
            if (is_file($chemin)) {
                require_once ($chemin);
            }
        }
    }
    
    static function genereFichierTraductions($file="", $code_language="") 
    {
        if ($code_language != "") {
            $code_language_todo = trim($code_language);
        }
                
        $chemin = BASE_PATH;
        if (!is_dir($chemin)) {
            mkdir($chemin, 0755, true);
            chmod($chemin, 0755);
        }		
        $chemin .= "/data/translation/";		
        if (!is_dir($chemin)) {
            mkdir($chemin, 0755, true);
            chmod($chemin, 0755);
        }	
        $chemin .= $code_language_todo."/";
        if (!is_dir($chemin)) {
            mkdir($chemin, 0755, true);
            chmod($chemin, 0755);
        }		
        
        $chemin .= $file;
        
        if (is_file($chemin)) {
            unlink ($chemin);
        }
        
        $chaine = "<?php\n";
        //Le global est toujours chargé, il ne faut pas redéfinir $GLOBALS['TRAD'] sur les autres fichiers
        if($file == "global.ini"){
            $chaine .= "\$GLOBALS['TRAD'] = array(); \n";
        }
        
        $tabTranslated 	= array();
        
        $sql = "SELECT LT.*
                    FROM translations LT 
                        LEFT JOIN languages L ON L.id = LT.id_language
                    WHERE fichier = '".$file."'
                    AND L.code = '".$code_language_todo."'";
        $tab_translations = Model::getDb()->requete($sql);
        foreach ($tab_translations as $trad) {
            if ($trad["fichier"] == "global.ini") {
                $chaine .= "\$GLOBALS['TRAD'][\"".trim($trad["translation_key"])."\"]";
                $chaine .= " = \"".str_replace("\"", "\\\"", $trad["translation"])."\";\n";
            } else {
                $file_tmp = explode(".", $trad["fichier"]);
                $file_tmp = strtoupper($file_tmp[0]);
                $chaine .= "\$GLOBALS['TRAD'][\"".trim($trad["translation_key"])."\"]";
                $chaine .= " = \"".str_replace("\"", "\\\"", $trad["translation"])."\";\n";
            }
            $tabTranslated[$trad["translation_key"]] = $trad["translation"];
        }	
        
        $chaine .= "?>";
        $fic = fopen($chemin, "a+");
        fwrite($fic, $chaine);		
        fclose($fic);

        chmod($chemin, 0755);

        echo "Le fichier ".$code_language_todo."/".$file." a bien été créé <br />";
    }
    
    static function genereAll() 
    {        
        $tablanguages = array();
        $sql = "SELECT DISTINCT code
                    FROM languages";
        $rsliste = Model::getDb()->requete($sql);
        foreach ($rsliste as $rs) {
            $tablanguages[$rs['code']] = true;
        }

        $sql = "SELECT DISTINCT fichier
                    FROM translations
                    WHERE IFNULL(fichier, '') <> ''";				
        $rsliste = Model::getDb()->requete($sql);
        foreach ($rsliste as $rs) {
            foreach ($tablanguages as $keylanguage=>$valuelanguage) {
                Langue::genereFichierTraductions($rs["fichier"], $keylanguage);		
            }
        }
    }
    
    public static function getCodeLanguage($id_language)
    {        
        $sql = "SELECT code, label
                    FROM languages
                    WHERE id = ".$id_language;
        $rsliste = Model::getDb()->requete($sql);
        
        return trim($rsliste[0]['code']);
    }

    public static function getLanguage($id_language)
    {        
        $sql = "SELECT label
                    FROM languages
                    WHERE id = ".$id_language;
        $rsliste = Model::getDb()->requete($sql);
        
        return trim($rsliste[0]['label']);
    }
    
    public static function getIdLanguage($code)
    {        
        $sql = "SELECT id, label
                    FROM languages
                    WHERE code = '".$code."'";
        $rsliste = Model::getDb()->requete($sql);
        
        return $rsliste[0]['id'];
    }
    
    public static function getAllLanguage() {        
        $sql = "SELECT id, code, label
                FROM languages";
        $tab_language 		= Model::getDb()->requete($sql);
        $result_language	= array();
        foreach($tab_language as $l) {
            $result_language[$l["id"]]	= $l["code"];
        }
        
        return $result_language;
    }

    public static function display($cle) 
    {
        if(isset($GLOBALS['TRAD'][$cle])){
            return utf8_encode($GLOBALS['TRAD'][$cle]);
        }else{
            return "##".$cle."##";
        }
    }
}
?>