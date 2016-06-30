Description
===========

Le role du composant `Response` est d'ecrire des reponses HTTP au navigateur. 

C'est lui qui va envoyer communiquer directement avec le navigateur pour afficher le code HTML, rediriger l'utilisateur, afficher une page d'erreur, enregistrer cookie/session etc...

Cet objet `Response` est accessible sur tous les controlleurs `FMUP\Controller` au moyen de la methode ``getResponse()``.

A partir de là, le developpeur est capable de définir les headers à envoyer ou le contenu a afficher au moyen des methodes `addHeader()` et `setBody()`. 
A titre d'exemple, le header de status 200 (la page est désservie) est disponible dans `\FMUP\Response\Header\Status`

Par exemple, pour afficher "coucou" avec un code de retour 200, il faut utiliser le code suivant dans un controlleur :

```php
$this->getResponse()
    ->addHeader(\FMUP\Response\Header\Status::TYPE, \FMUP\Response\Header\Status::VALUE_OK)
    ->setBody('coucou');
```

FMUP etant encore jeune, il est necessaire de faire évoluer le contenu du package `FMUP\Response\Header` pour implementer la [[rfc2616|?http://tools.ietf.org/html/rfc2616]]
