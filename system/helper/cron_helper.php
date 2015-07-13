<?php
/**
 * Classe permettant de gérer tous les appels de cron via une table
 * Un cron appellera la fonction executer une fois par minute, qui déterminera les crons à lancer
 * @version 6.0
 * @author afalaise
 */
abstract class CronHelper
{
    final public function executer ()
    {
        // On indique comme bloqué les crons qui sont en cours depuis trop longtemps
        Cron::bloquerJetons();
        
        $crons = Cron::findAll(array('etat IN ('.Constantes::getEtatCronLibre().', '.Constantes::getEtatCronBloque().')'));
        
        foreach ($crons as $cron) {
            // Cron actif et libre
            if ($cron->getEtat() == Constantes::getEtatCronLibre()) {
                if ($cron->testerPeriodicite($cron)) {
                    $this->setDebug('Cron lancé : '.$cron->getFonction());
                    $cron->lancer();
                    if (method_exists($this, $cron->getFonction())) {
                        call_user_func(array($this, $cron->getFonction()));
                    }
                    $cron->liberer();
                    $this->setDebug('Cron terminé : '.$cron->getFonction());
                }
                
            // Cron bloqué, on prévient et on indique qu'on a prévenu
            } else {
                $this->setDebug('Cron déclaré bloqué : '.$cron->getFonction());
                $this->envoyerMailBlocage($cron);
                $cron->bloquer();
            }
        }
    }

    public function envoyerMailBlocage ($cron)
    {
        
    }
    
    public function setDebug ($message)
    {
        echo Date::today(true).' : '.$message.'<br/>';
    }
}