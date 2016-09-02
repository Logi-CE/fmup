Description
===========

ErrorController etait lors d'une precedente version, le composant de controller permettant la gestion d'erreur. Avec l'arrivée du ErrorHandler, Le composant etait devenu obsolète mais peut toujours etre utilisé en l'implementant comme plugin du ErrorHandler.

Le ErrorController intervient pour afficher une page sur le navigateur lorsqu'une erreur intervient (afin d'afficher un message user-friendly par exemple, ou gerer les pages 403, non authorisée etc...)

L'erreur survenue est recuperée grâce a la methode `getException` du controller. Dans le cas d'une erreur de type `\FMUP\Exception\Status`, le code HTTP est également envoyé au navigateur
