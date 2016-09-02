Description
===========

Les composants [[PreDispatch|PreDispatch / PostDispatch]] et [[PostDispatch|PreDispatch / PostDispatch]] du framework FMUP sont en faire deux instances differentes du meme composant `FMUP\Dispatcher`.
Ce composant a pour objectif de modifier le contenu des objets `Response` et `Request` avant et apres le traitement du controlleur.

Typiquement, grace au dispatcher, une requete va pouvoir retre redirigée en interne avant d'atteindre un controlleur initialement ciblé. Par ailleurs, une reponse peut deja commencer a preparer des headers pour la reponse avant ou apres l'action d'un controlleur. Par exemple en encodant tout le resultat en JSON.

Fonctionnement
==============

Le fonctionnement des `Dispatcher` est basique. 
On ajoute autant de `FMUP\Dispatcher\Plugin` que necessaire dans le `PreDispatch` ou le `PostDispatch` et ceux ci agissent sur la `Request` ou la `Response` dans l'ordre d'insertion.

Par défaut, il existe le Dispatcher `FMUP\Dispatcher\Post` qui s'execute en `PostDispatch` qui inclue le plugin `FMUP\Dispatcher\Plugin\Render` pour faire le rendu de la `Response` au navigateur.
