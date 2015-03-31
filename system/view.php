<?php
class View
{
    /**
     * La vue à afficher
     **/
    protected $vue;
    /**
     * Le layout à afficher
     **/
    protected $layout;
    /*
     * Afficher bandeau de droite ou non dans le layout
     */
    protected $withoutBandeau;
    /**
     * Les css à inclure dans la page
     **/
    protected $styles;
    /**
     * Les javascripts à inclure dans la page
     **/
    protected $javascripts;
    /**
     * Le titre de la page
     *
     * @var String
     */
    protected $title;

    /**
     * Effectue le rendu d'une vue
     * @param String la vue à charger (sans l'extension .php)
     * @param Array un tableau de paramètres à transmettre à la page
     * @param Array un tableau d'options (optionelles)
     *				* layout			: le layout à utiliser (si pas celui par défaut)
     *				* css				 : les css à utiliser dans la vue
     *				* javascripts : les javascripts à utiliser dans la vue
     **/
    public function __construct($vue, $params = array(), $options = array())
    {
        // Gestion des fichiers de langue
        if (Config::getIsMultilingue()) {

            if(!isset($GLOBALS["codelanguagedefaut"])){
                //On récupère la langue du navigateur
                $langue_nav = explode(',',getenv('HTTP_ACCEPT_LANGUAGE'));
                $langue_nav = strtoupper(substr(chop($langue_nav[0]),0,2));
                $GLOBALS["codelanguagedefaut"] = trim($langue_nav);
            }

            if(isset($_SESSION["codelanguagedefaut"])){
                $GLOBALS["codelanguagedefaut"] = $_SESSION["codelanguagedefaut"];
            }
            // charger les fichiers de langue
            $translation_include    = array();
            $translation_include[]  = "global.ini";

            $dossier = preg_split('[/]',$vue);
            if(isset($dossier[0])){
                $translation_include[]  = $dossier[0].".ini";
            }

            foreach($translation_include as $translation) {
                Langue::chargeFile($translation);
            }
        } else {
            // On initialise la variable quand même au cas où un paramètre de config aurait été oublié
            $GLOBALS["codelanguagedefaut"] = 'FR';
        }

        $varappli = Config::paramsVariables('');

        $layout = call_user_func(array(APP, "defaultLayout"));
        if (isset($varappli['styles'])) {
            $styles = $varappli['styles'];
        } else {
            $styles = call_user_func(array(APP, "defaultCSS"));
        }

        if (isset($varappli['javascripts'])) {
            $javascripts = $varappli['javascripts'];
        } else {
            $javascripts = call_user_func(array(APP, "defaultJavascripts"));
        }
    	/**
    	 * 19/11/2014 : modification du traitement du js
    	 * l'option javascripts_default permet de remplacer totalement la liste des js par défaut
    	 * pour certains cas spécifiques où on ne veut pas utiliser certains fichiers js
    	 * par ex : pour l'utilisation de différentes versions de jquery non compatibles avec des js passé en options
    	 */
        if(!empty($options['javascripts_default_forced'])){
			$javascripts = $options['javascripts_default_forced'];
        }

        if (!isset($params['popup'])) {
            $params['popup'] = false;
        }
        if (!isset($params['exporter'])) {
            $params['exporter'] = false;
        }

        $this->vue	 = '../../application/'.APPLICATION.'/view/'.$vue.'.php';

        $this->withoutBandeau = false;
        if(isset($options['withoutBandeau'])){
            $this->withoutBandeau = true;
        }

        // si on n'est pas en train d'afficher une popup
        if (!$params['popup'] && !$params['exporter']) {

            // récupération de l'option layout
            if (isset($options['layout'])) {
                $this->layout = "../../application/".APPLICATION."/view/layout/".$options["layout"].".php";
            } else {
                $this->layout = "../../application/".APPLICATION."/view/layout/".$layout.".php";
            }

            // récupération de l'option styles
            if (isset($options['styles'])) {
                $this->styles = array_merge($styles, $options['styles']);
            } else {
                $this->styles = $styles;
            }
            if (isset($_GET['sys']) && file_exists(BASE_PATH.'/public/'.APPLICATION.'/styles/'.$_GET['sys'].'.css')) {
                $this->styles[] = $_GET['sys'];
            }

            // récupération de l'option javascripts
            if (isset($options['javascripts'])) {
                $this->javascripts = array_merge($javascripts, $options['javascripts']);
            } else {
                $this->javascripts = $javascripts;
            }
            if (isset($_GET['sys']) && file_exists(BASE_PATH.'/public/'.APPLICATION.'/scripts/'.$_GET['sys'].'.js')) {
                $this->javascripts[] = $_GET['sys'];
            }
            $this->title = call_user_func(array(APP, "titleApplication"));
            if (isset($params['titre'])) {
                $this->title .= '&nbsp;&ndash;&nbsp;'.$params['titre'];
            }

        }

        // récupération de toutes les variables à poster au layout
        extract($params);

        //include de la page
        if ($params['popup'] || $params['exporter']) {

            if ($params['exporter']) {
                DownloadHelper::getHeaders($params['titre'].'.'.$params['exporter'], $params['exporter']);
            }

            //popup, on ne met pas le layout
            if (file_exists($this->vue)) {
                include ($this->vue);
            } else {
                throw new Error(Error::vueIntrouvable($this->vue));
            }

        } else {

            //page standard, ajout du layout
            if (file_exists($this->layout)) {
                if (file_exists($this->vue)) {
                    include ($this->layout);
                } else {
                    throw new Error(Error::vueIntrouvable($this->vue));
                }
            } else {
                throw new Error(Error::layoutIntrouvable($this->layout));
            }
        }

    }
}
