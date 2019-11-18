<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Message1;

class ModifController extends AbstractController
{
    /**
     * @Route("/modif", name="modif")
     */
    public function index()
    {
        return $this->render('modif/index.html.twig', [
            'controller_name' => 'ModifController',
        ]);
    }
    
    
    /**
     * @Route("/modif/Visiteur", name="modifV")
     */
    public function modifV(Message1 $msg)
    {
        
        $visiteur = $msg->getLMMessage("azerty", "admin");
        
        $this->addFlash("Visiteur : ", $visiteur);
        
        return $this->render('modif/index.html.twig', [
            'controller_name' => 'ModifController',
        ]);
    }
}
