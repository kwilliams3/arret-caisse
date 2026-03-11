<?php
/**
 * Created by PhpStorm.
 * User: Poizon
 * Date: 24/10/2015
 * Time: 14:08
 */

namespace Core\Model;


use DateTime;

class Model {

    public static function CalculTemps($dateValidation){
        $date_actuelle = new DateTime();
        $dateProbable = new DateTime($dateValidation);
        $intervalle = $date_actuelle->diff($dateProbable);
        $mois = $intervalle->format('%m');
        $result = ' ';
        if($mois == 0){
            $annee = $intervalle->format('%y');
            $result = $annee.'_Annee';
        }else{
            $result = $mois.'_Mois';
        }

        return $result;
    }

    public static function CalculReaction($dateDebut,$dateFin){
        $debut = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        $intervalle = $debut->diff($fin);
        $jours = $intervalle->format('%d');

        return $jours;
    }

}