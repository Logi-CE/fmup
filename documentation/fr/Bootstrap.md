Principe
========

Le bootstrap est le tout premier système à etre executé à l'appel du Framework. Il est appelé avant la boucle de [[PreDispatch|PreDispatch / PostDispatch]]. Le but de ce composant est de preconfigurer certains composants, comme par exemple la gestions des logs, de la base de donnée etc...

Attention neamoins, pour le cas de la base de donnée par exemple, il est interessant de configurer une instance avec un couple login/mot de passe mais pas de faire la connexion. Cette connexion ne devra etre faite que si necessaire.

Implementation
==============

Un bootstrap par defaut existe au sein de FMUP (`\FMUP\Bootstap`).
C'est ce composant qui est appelé si un composant specifique n'est pas parametré au niveau du Framework grace a la methode `setBootstrap`.
 
La methode `warmUp` est alors appelée. C'est cette methode qui doit parametrer chaque composant. 
Par defaut, le Bootstrap parametre un composant Logger.

Le composant `Bootstrap` peut etre recuperé au sein des controllers grace a l'implementation `$this->getBootstrap()`. Ainsi, il est possible de recuperer le Logger au sein d'un Controller grâce a l'implementation `$this->getBootstrap()->getLogger()`.
