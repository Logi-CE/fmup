Description
===========

Le ErrorHandler est un composant permettant de gerer les erreurs déclenchées sur le projet. Il est possible d'ajouter des `ErrorHandler\Plugin` au `ErrorHandler` qui seront executés les uns a la suite des autres dans l'ordre d'insertion avec le process suivant:

* methode `canHandle`
* Si la methode `canHandle` retourne `true`
    * la methode `handle`du plugin est appelée et l'erreur peut etre traitée

ErrorHandler\Plugin\Abstraction
-------------------------------

Tous les __Plugins__ developpés pour le `ErrorHandler` doivent heriter de `FMUP\ErrorHandler\Plugin\Abstraction`.

Cette classe permet d'apporter les Composants [[Bootstrap]], [[Response]] et [[Request]] a chaque plugin. De plus, l'erreur déclenchée peut etre recuperée sous forme d'exception grace a la methode `getException`

Par défaut, FMUP propose la configuration du ErrorHandler suivante dans la classe `FMUP\ErrorHandler\Base`:

```php
<?php
namespace FMUP\ErrorHandler;


class Base extends \FMUP\ErrorHandler
{
    public function __construct()
    {
        $this
            ->add(new \FMUP\ErrorHandler\Plugin\HttpHeader())
            ->add(new \FMUP\ErrorHandler\Plugin\Log())
            ->add(new \FMUP\ErrorHandler\Plugin\Mail())
        ;
    }
}
```

A titre d'exemple, le plugin `FMUP\ErrorHandler\Plugin\HttpHeader` permet de retourner un code d'erreur 500 si l'erreur n'est pas une erreur de status (c'est a dire `FMUP\Exception\Status` qui est déjà gérée par le ErrorController + plugin `FMUP\ErrorHandler\Plugin\ErrorController`)

###ErrorHandler\Plugin\Plugin\HttpHeader

```php
<?php
namespace FMUP\ErrorHandler\Plugin;

use FMUP\Response\Header\Status;

class HttpHeader extends Abstraction
{
    public function canHandle()
    {
        return (!$this->getException() instanceof \FMUP\Exception\Status);
    }

    public function handle()
    {
        $this->getResponse()->setHeader(new Status(Status::VALUE_INTERNAL_SERVER_ERROR));
        return $this;
    }
}
```
