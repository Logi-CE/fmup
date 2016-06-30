Description
===========

Le composant `FMUP\View` sert, comme son nom l'indique, à faire du rendu HTML. 
Il n'est volontairement pas rétrocompatible avec le composant View de FMU car l'ancien composant inclue forcément un layout défini à un path très particulier et possède plusieurs roles dont ce composant devrait se passer.

Le nouveau composant `FMUP\View` n'intègre pas de layout par défaut, c'est d'ailleurs grâce à cela qu'il est possible d'imbriquer plusieurs vues entre elles afin de réutiliser des portions de code HTML (un rendu d'une arborescence d'offre/produit par exemple).

Le composant `FMUP\View` est par contre utilisable à l'interieur de l'ancien système de vue:

__exemple d'utilisation en concurrence__

```php
public function index()
{
    $offres = new \LogiCE\Offre\Liste();
    $id = $this->getRequest()->get('id', 0);
    $params['offreView'] = new \FMUP\View(array('offres' => $offres->getTree($id)));
    new \View("comptoir/index", $params, $options);
}
```
__utilisation de FMUP\View dans une vues PHP en conccurence__

```php
<div id="arbre_offres">
    <?php
 echo $params['offreView']->setViewPath(BASE_PATH . '/lib/LogiCE/Offre/Liste/View/select.phtml')->render();
 ?>
</div>
```

Dans l'exemple ci dessus :

* On recupere les offres `$offres->getTree($id))`
* On enregistre cette liste d'offre dans une variable de vue 'offre' `array('offres' => $offres->getTree($id))`;
* On cree une FMUP\View avec les variables de vues specifiques `new \FMUP\View(array('offres' => $offres->getTree($id)));`
* On passe cette vue au systeme de vue actuelle dans la variable 'offreView' `$params['offreView'] = new \FMUP\View(array('offres' => $offres->getTree($id)));`

Dans la vue:

* On recupere notre FMUP\View dans la variable 'offreView' `$params['offreView']`
* On défini le rendu à afficher `$params['offreView']->setViewPath(BASE_PATH . '/lib/LogiCE/Offre/Liste/View/select.phtml')`
* On affiche le retour `echo $params['offreView']->setViewPath(BASE_PATH . '/lib/LogiCE/Offre/Liste/View/select.phtml')->render();`

Ce qu'il se passe : 

Le composant `FMUP\View` va chercher la vue `BASE_PATH . '/lib/LogiCE/Offre/Liste/View/select.phtml'` et l'interprete. 

Dans un template de rendu de `FMUP\View`, les variables sont accessibles grace à la methode `$this->getParam($nomParam)`. Il est donc possible d'afficher notre liste d'offre grâce au code `var_dump($this->getParam('offres'));`
