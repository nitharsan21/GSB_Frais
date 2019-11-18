<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comptable;
use App\Entity\Visiteur;
use App\Entity\LigneFraisForfait;
use App\Repository\ComptableRepository;
use App\Form\LoginComptableType;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Repository\LigneFraisForfaitRepository;


class ComptableController extends AbstractController
{
    
    
    /**
     * @Route("/comptable", name="comptable")
     */
    public function index()
    {
        return $this->render('comptable/index.html.twig', [
            'controller_name' => 'ComptableController',
        ]);
    }
    

    /**
     * @Route("/LoginComptable", name="LoginComptable")
     */
    public function LoginComptable(Request $query)
    {   
        $visiteur = new Comptable();
        $summitt = false;
        $form = $this->createForm(LoginComptableType::class, $visiteur);
        
        $form->handleRequest($query);
        if ($form->isSubmitted() && $form->isValid()) {
                
            $login = $form['login']->getData();
            $password = $form['mdp']->getData();
            $lesComptable = new Comptable();
            $lesComptable = $this->getDoctrine()->getRepository(Comptable::class)->LoginComptable($login,$password);
            

            if($lesComptable != null){
                    
                    $session = new Session();
                    $session->set('user_nom',$lesComptable->getNom());
                    $session->set('user_prenom',$lesComptable->getPrenom());
                    $session->set('login',"Salut");
                    $_SESSION['login'] = true;
                    return $this->redirect('CompteComptable');
            }
            $summitt = true;
        }
        return $this->render('comptable/LoginComptable.html.twig',array('form'=>$form->createView(),'essie'=>$summitt,));
    }
    
    /**
     * @Route("/CompteComptable", name="CompteComptable")
     */
    public function CompteComptable()
    {   
        if(isset($_SESSION['login'])){
            if($_SESSION['login'] == true){
                return $this->render('comptable/VueAccueilComptable.html.twig');
            }
        }
        return $this->redirect('LoginComptable');
    }
    
    
    /**
     * @Route("/deconnexionCompteComptable", name="deconnexionCompteComptable")
     */
    public function deconnexionCompteComptable()
    {   
        if(isset($_SESSION['login'])){
            if($_SESSION['login'] == true){
                $_SESSION['login'] = false;
                return $this->redirect('LoginComptable');
            }
        }
        return $this->redirect('LoginComptable');
    }
    
    
    
    /**
     * @Route("/VueValiderFrais", name="VueValiderFrais")
     */
    public function VueValiderFrais(Request $query)
    {
        if(isset($_SESSION['login'])){
            if($_SESSION['login'] == true){
                $idv = null;
                $lesVisiteur = $this->getDoctrine()->getRepository(Visiteur::class)->findall();
                if($query->isMethod('POST')){
                    if($query->request->get('Visiteur') != null){
                        $mois = null;
                        if(date('j') > 15 ){
                            $mois = date("F", strtotime("+1 month", strtotime(date("F") . "1")) ); 
                        }
                        else{
                            $mois = date('F');
                        }
                        
                        $idv = $query->request->get('Visiteur');
                        
                        $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
                        
                         /*   $lignehff = $this->getDoctrine()->getRepository(\App\Repository\LigneFraisHorsForfaitRepository::class)->();
                         *
                         */
                        return $this->render('comptable/FicheFrais.html.twig' , [ 'visiteurs' => $lesVisiteur,'mois' => $mois ,'idvisiteurChoise' => $idv,'l' => true,'lff' => $ligneff]);
                    }
                        return $this->render('comptable/FicheFrais.html.twig' , [ 'visiteurs' => $lesVisiteur,'mois' => $mois ,'l' => true , 'idvisiteurChoise' => $idv]);
                }



                return $this->render('comptable/FicheFrais.html.twig' , [ 'visiteurs' => $lesVisiteur, 'l' => false,'idvisiteurChoise' => $idv,]);
            }
        }
        return $this->redirect('LoginComptable');
        
        
    }
    
    
    
    /**
     * @Route("/FicheFrais", name="FicheFrais")
     */
    public function FicheFrais()
    {
        $lesVisiteur = $this->getDoctrine()->getRepository(\App\Entity\Visiteur::class)->findall();
        
        
        
    }
    
    
}
    
    
    
    

