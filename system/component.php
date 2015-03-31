<?php
class Component
{
    /**
     * Le résultat du componsant
     * @var {String}
     */
    private $content;

    /**
     * Effectue le rendu d'un composant (sorte de vue partielle)
     * @param {String} le controler à charger (sans l'extension .php)
     * @param {String} le nom de la variable dans le composant
     * @param {String} la valeur de la variable
     * @param {Boolean} Est-ce qu'on lance en mode silencieux pour ne pas l'afficher directement.
     **/
    public function __construct($composant, $variable_name = "tmp", $variable_value = null, $silent = 0)
    {
        $this->component = BASE_PATH."/lib/$composant.php";

        $$variable_name = $variable_value;

        if ($silent) {
            ob_start();
        }

        if (file_exists($this->component)) {
            include_once ($this->component);
        } else {
            throw new Error(Error::composantIntrouvable($this->component));
        }

        if ($silent) {
            $this->content = ob_get_contents();
            ob_end_clean();
        }
    }

    /**
     * Retourne le résultat du composant
     * @return {String}
     */
    public function getContent()
    {
        return $this->content;
    }
}
