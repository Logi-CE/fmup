Description
===========

Le composant Cache va nous permettre de stocker toutes les données que nous souhaitons dans un ou plusieurs systemes de cache. En effet, nous pouvons instancier autant d'objets de cache que nous le souhaitons.
Il est important de comprendre la problematique liée à un cache. 
Certes, celle si devrait ameliorer les performances dans tel ou tel cas mais il est necessaire de savoir quand appeler un cache, quand l'invalider ou le rafraichir, bien gerer ses clefs de caches etc...

La logique de gestion de cache est accessible grace au composant `Cache`.

L'interface \FMUP\Cache\CacheInterface
--------------------------------------

Il est possible d'implementer son propre driver de `Cache` en implementant l'interface `\FMUP\Cache\CacheInterface`.

### Exemple d'utilisation
####Stockage des droits
```php
public function getAll()
{
    $sql = 'SELECT * FROM MYTABLE';
    $cacheKey = 'MYTABLE_datas';
    if ($this->getCache()->has($cacheKey)) {
        return $this->getCache()->get($cacheKey);
    }
    $results = $this->getDb()->fetchAll($sql);
    $this->getCache()->set($cacheKey, $results);
    return $results;
}
```

Dans un cas comme celui la, nous faisons toujours la meme requete en Db. 

Si les données varient peu, nous allons optimiser notre logique en y appliquant un cache qui sera chargé si le cache n'as pas été invalidé.

La requete ne sera donc executée que si necessaire et ira rafraichir le cache.

###Le driver Ram (\FMUP\Cache\Driver\Ram)

Le premier driver à etre implémenté dans FMUP est un cache simple en RAM. Celui ci est automatiquement invalidé à la fin du traitement.

Le seul cas ou ce cache sera réellement pertinent et efficace sera lorsqu'une meme logique est appliquée plusieurs fois dans la meme page. 
C'est par exemple le cas des ACL lorsque plusieurs droits sont vérifiés sur la meme page.

###Le driver File (\FMUP\Cache\Driver\File)

Le cache File permet de stoquer le resultat d'un calcul dans un fichier. Le système de fichier etant le moins efficace des systèmes de cache (temps d'acces disque dur), ce driver devra necessairement etre appliqué lorsque les conditions seront réunis.
Quelques cas d'exemple: génération d'un fichier PDF dynamique, resize d'une image à la volée, concatenations/traitement de plusieurs fichiers etc...?
