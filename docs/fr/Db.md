Quickstart
==========

Package FMUP
------------

Les connexions à une base de donnée sont reccurentes dans les projets. Afin d'eviter les dérives communes de ce genre de composants (SQL écrit en dur dans des composants autre que les models par exemple), plusieurs principes ont été mis en place sur FMUP.

###Le composant FMUP\Db
Ce composant herite directement de celui de FMU afin de conserver la compatiblité avec l'existant. Il est typique du design pattern __Façade__.

Ce composant créé donc une instance interne d'un driver de base de donnée grace à une `Factory` (design pattern également)

###Le composant FMUP\Db\Factory

Ce composant permet de charger un composant qui sera compatible avec la `Db`. Concrètement, Chaque driver de `Db` est séparé pour la construction des requetes (~MySQL et MSSQL par exemple) et la Factory se charge de créer le bon composant et s'assure que ce composant sera utilisable dans l'objet `Db`.

###Le composant FMUP\Db\Driver\Pdo
PDO est actuellement le seul driver compatible sur Logi-CE implémenté dans FMUP. Potentiellement, il est possible de creer d'autres systemes de connexion à la base de donnée a partir du moment ou ils implémentent l'interface `FMUP\Db\Driver\~DbInterface`

Package FMU
-----------
###Helper DbHelper

Ce helper à pour vocation de mettre en place le bon driver de connexion à la base de donnée en fonction de la version PHP et du driver parametré dans la configuration.

Le driver sera configuré avec les informations de configuration.

Le DbHelper integre egalement le design pattern __Singleton__ qui permet donc de n'avoir qu'une seule et unique instance de base de donnée.

Voici son fonctionnement :

* Si la version de PHP est supérieur ou égale à la version 5.3
    * alors on cree une instance de FMUP\Db (voir plus haut)
* Sinon
    * Dans le cas ou la configuration impose une connexion MSQL
        * on crée un composant DbConnection (FMU connexion a une base de donnée MSSQL)
    * Dans tous les autres cas
        * on crée un composant DbConnectionMysql (FMU connexion a une base de donnée MySQL)
