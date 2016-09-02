Description
===========

Le composant `Request` a pour but d'analyser une requete et d'offrir de maniere perene le resultat de cette analyse sous forme d'objet. 

Http
----

Le composant `Request\Http` implemente l'analyse d'une requete HTTP.

Concretement, cela permet de recuperer les GET/POST etc sans utiliser la superglobale au sein du code. 

Grace a ce composant, il sera ainsi possible de determiner si l'utilisateur a fait une requete Ajax, un post, un upload de fichier, son navigateur, s'il possede tel ou tel composant, ses langues etc...

Cli
---

En mode CLI (Command Line Interface), Le composant `Request\Cli` analyse les parametres passés au script grace a la syntaxe [[GetOpt|http://php.net/getopt]]. Il faut définir ces parametres grace a la methode `defineOpt` avant utilisation. Le parametre __--route__ est utilisé pour diriger une requete.

Comment le recuperer et/ou l'utiliser ?
=======================================

Dans un controlleur, il est possible d'heriter du controlleur `\FMUP\Controller` afin de pouvoir recuperer la requete (apres modification le cas echeant apres le systeme de routing) grace à la methode `getRequest()`

Dans les autres cas, il est possible de faire un `new \FMUP\Request\Http()` afin de beneficier de ses fonctionnalitées.

__CETTE PRATIQUE N'EST PAS DU TOUT CONSEILLEE ! La requete devrait toujours n'être accessible que depuis un controlleur ou a un niveau supérieur (Route/Routing/Post-Traitement....).__


