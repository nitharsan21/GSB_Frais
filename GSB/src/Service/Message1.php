<?php
namespace App\Service;
use App\Entity\Visiteur;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Message
 *
 * @author etudiant
 */

class Message1 {
    //put your code here
    public function getLMMessage($Mdp, $login)
    {
        $message = "Le login".$login." à pour mdp: ".$Mdp;
        
        return $message;
    }
}
