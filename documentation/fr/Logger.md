Principe
========

Dans tout projet, il est necessaire de logger certaines etapes d'une ou plusieurs manieres sur un ou plusieurs canaux. Le role du composant `Logger` est de gerer cette problematique.

Il est possible d'avoir plusieurs canaux de log. 

Chaque canal sera ensuite parametré pour enregistrer les informations dans un fichier en particulier et/ou envoyer un mail et/ou envoyer un SMS etc... Le tout en fonction du degré d'importance de l'information à logger.

Ce composant utilise en interne le package ``Monolog``.

Degres
------

Les degrés de notifications sont basés sur la PSR-3 et sont implementés dans ``Monolog``. Les niveaux sont triés dans cet ordre de la moins importante à la plus importante:

* DEBUG
* INFO
* NOTICE
* WARNING
* ERROR
* CRITICAL
* ALERT
* EMERGENCY

Parametrage de base
-------------------

Par défaut, Le composant `Logger` paramètre deux canaux, le canal __ERROR__ (`\FMUP\Logger::ERROR`) et le canal __SYSTEM__ (`\FMUP\Logger::SYSTEM`)
Il est tout a fait possible, et meme conseillé, de creer ses propres canaux pour verifier le bon fonctionnement d'un composant en particulier

###Canal ERROR

C'est sur ce canal qu'il faut faire transiter une information logicielle importante. Si le degré du log est supérieur ou egal à `\Monolog\Logger::CRITICAL`, alors un e-mail sera envoyé au support.

Toute notification inferieur ne sera pas traitée.

###Canal SYSTEM

C'est sur ce canal qu'il faut faire transiter toutes les informations logicielles systeme. Qu'importe le degré de log, l'information sera stoquée dans le fichier __error_log__ (qui est parametré dans FMUP avec un nom de fichier par jour à la date du jour).

Implementation
--------------

Quelques exemples seront plus parlants :

```php
class Controller extends \FMUP\Controller
{
     public function render()
     {
          $value = 1;
          $logger = $this->getBootstrap()->getLogger(); //recupere le logger
          $logger->log(Logger\Channel\Error::NAME, 'ok', Logger::DEBUG); //ajoute le debug 'ok' sur le canal ERROR
          $logger->log(Logger\Channel\Error::NAME, 'value ' . $value, Logger::DEBUG); //ajoute le debug 'value 1' sur le canal ERROR
          $logger->log(Logger\Channel\System::NAME, 'value ' . $value, Logger::CRITICAL); //ajoute le critical 'value 1' sur le canal SYSTEM
          $logger->log(Logger\Channel\System::NAME, 'something went wrong but I can handle that', Logger::WARNING); //ajoute un warning sur le canal SYSTEM
     }
}
```
