[![Build Status](https://scrutinizer-ci.com/g/Logi-CE/fmup/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Logi-CE/fmup/build-status/master)
[![Latest Stable Version](https://poser.pugx.org/fmup/fmup/v/stable)](https://packagist.org/packages/fmup/fmup) 
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg?style=flat-square)](https://php.net/)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.0-8892BF.svg?style=flat-square)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Logi-CE/fmup/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Logi-CE/fmup/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Logi-CE/fmup/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Logi-CE/fmup/?branch=master)
[![License](https://poser.pugx.org/fmup/fmup/license)](https://packagist.org/packages/fmup/fmup)

FMUP
====

Easy to handle Framework

Documentation
=============

Full documentation available on (Wiki)[https://github.com/Logi-CE/fmup/wiki]

Synopsis
========
FMUP s'appuie sur un fork de la version 1.0.0.6 de FMU.

Les versions de FMU ne respectaient pas SEMVER, aussi, les releases mineurs / bug fix de FMU ne sont pas compatibles entre elles.

De part ce constat, FMUP nait pour rectifier le tir.

Overview
========

![Dispatch](http://logi-ce.github.io/fmup/dispatch.png)

Lorsque le navigateur envoie une requete, celle ci passe par les étapes suivantes :

* Elle est utilisable grâce à l'objet [[Request]]. 
* Le Bootstrap est appelé et l'objet Request est passé en parametre de celui ci.
* Cet objet Request passe dans la boucle de [[PreDispatch|PreDispatch / PostDispatch]]
* Puis elle passe dans la boucle de [[Routing]]
* Si la requete match avec une regle de routing, la boucle s'arrete et l'on va instancier le bon controlleur. Dans le cas contraire, l'on passe par le fallback de FMU qui va essayer de trouver le controlleur/action dans le dossier application. En cas d'echec, la page 404 est appelée selon la règle FMU.
* Le controlleur est instancié
* sa methode `preFiltre` est appelée
* puis la methode de l'action est appelée (suffixé par Action dans le cas d'un `FMUP\Controller`)
* puis la methode `postFiltre` est appelée
* Si l'action renvoi une chaine de caractere ou NULL, son contenu est ajouté dans le corps de l'objet [[Response]]
* Si l'action renvoi un objet [[View]], cette vue est rendue dans le corps de l'objet [[Response]]
* L'objet [[Response]] passe dans la boucle de [[PostDispatch|PreDispatch / PostDispatch]]
* L'objet [[Response]] est rendu. Cet objet peut etre manipulé à n'importe quelle étape du process.
* En cas d'erreur lors de ce process, le [[ErrorHandler]] est appelé
