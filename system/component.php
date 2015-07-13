<?php
/**
 * Class Component - used to load a 'lib' library. You might want to use composer
 * @deprecated
 * @see composer
 */
class Component
{
    /**
     * Le résultat du composant
     * @var string
     */
    private $content;

    /**
     * Effectue le rendu d'un composant (sorte de vue partielle)
     * @param string $composant : le controler à charger (sans l'extension .php)
     * @param string $variable_nom : [OPT] le nom de la variable dans le composant
     * @param string $variable_valeur : [OPT] la valeur de la variable
     * @param bool $silent : [OPT] Est-ce qu'on lance en mode silencieux pour ne pas l'afficher directement, par défaut non.
     **/
    public function __construct($composant, $variable_nom = "tmp", $variable_valeur = null, $silent = false)
    {
        $this->component = __DIR__."/component/$composant.php";

        $$variable_nom = $variable_valeur;

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
     * @return string : Le composant
     */
    public function getContent()
    {
        return $this->content;
    }
}
