Synopsis
========

Afin de mettre en place un systeme de module sur votre projet, il est necessaire de pouvoir analyser les requêtes HTTP et de déterminer par quel controlleur/action, la rêquete doit être traitée.

FMU offrait un système de base sur ce principe:

Partant de ce postulat, un composant de routing permet desormais de prendre en charge les types d'URL dans notre code et d'y appliquer le comportement désiré.

Le routing de base existe toujours au niveau de FMU. La couche d'abstraction du routing est ajoutée au niveau de FMUP.

Mise en place
=============

Au niveau du bootstrap, nous declarons au framework que nous allons utiliser un systeme de routing

__bootstrap__

```php
$framework = new FMUP\Framework();
$framework
 ->setRoutingSystem(new \LogiCE\Routing\Front())
 ->initialize();
```

Dans cet exemple, nous utilisons le systeme de routing spécifique au Front (`LogiCE\Routing\Front`). Cette classe hérite du composant `\FMUP\Routing` qui est attendu au niveau du framework.

Le Routing spécifique
---------------------

Dans l'exemple précedant, le systeme de routing implémenté herité d'un systeme de routing commun qui lui meme est basé sur le composant `Routing` de FMUP.

Le role du composant de routing est de gerer plusieurs regles de routing (appelées route) et de renvoyer la règle applicable. 

Ainsi, il est possible de définir la liste des règles à appliquer lorsqu'une URL est appelée.

__exemple du systeme de routing du front__

```php
namespace LogiCE\Routing;
use LogiCE\Routing\Route\Front as Route;

class Front extends Common
{
 public function __construct()
 {
  $this->addRoute(new Route\Produit());
  parent::__construct();
 }
}
```

Dans le cas ci-dessus, nous ajoutons une règle de gestion pour les URL produits du front `$this->addRoute(new LogiCE\Routing\Route\Front\Produit());`

Les règles sont testées une à une dans l'ordre d'insertion des routes. La règle la plus haute est donc la regle qui sera appliquée en premier, si cette regle est applicable. Les routes ajoutées à la suite ne seront pas testées.

Lorsque cette règle sera applicable, le `Framework` saura rediriger le code vers le bon controlleur et la bonne action.

La règle de route
-----------------

Une règle de route doit obligatoirement hériter de la classe `FMUP\Routing\Route` pour etre compatible avec le système de routing. Cette classe impose l'écriture de 3 methodes.

Ces methodes sont :

* canHandle
* getControllerName
* getAction

`canHandle` permet de definir si la regle est applicable.

Si elle l'est, le comportement de la fonction `handle` sera executé et le framework executera l'action retourné par `getAction` du controlleur dont la classe est retourné par `getControllerName`.

Un objet de requete est accessible dans les routes via la methode `getRequest` permettant de faire des match sur les URLs.
Exemple de la règle de route produit du front

```php
<?php
namespace LogiCE\Routing\Route\Front;

/**
 * Class Produit - Route handling for product in front
 * @package LogiCE\Routing\Route\Front
 */
class Produit extends \FMUP\Routing\Route
{
 /**
 * This route can only be applied in front module
 */
 const MODULE = 'front';

 /**
 * Must return true if URI can be handled by route
 * @return bool
 */
 public function canHandle()
 {
  if (APPLICATION != self::MODULE) {
  return false;
 }
  $result = preg_match('~/produit/(.+)~', $this->getRequest()->getRequestUri(), $matches);
  return (bool) $result;
 }

 /**
 * Must return action to call
 * @return string
 */
 public function getAction()
 {
  return 'index';
 }

 /**
 * Must return Controller class name
 * @return string
 */
 public function getControllerName()
 {
  return 'CtrlProduit';
 }
}
```

Si l'url appelée match selon le pattern __/produit/(.+)__. (par exemple : ~http://demo.foo.bar/produit/test). alors `canHandle` renvoi `true` et la regle est appliquée.

La methode index du controller `CtrlProduit` sera appelée. (potentiellement, la methode `getControllerName()` peut retourner la chaine `\LogiCE\Module\Front\Controller\Produit` et le controlleur sera appelé en conséquence.
