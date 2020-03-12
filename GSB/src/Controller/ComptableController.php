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
use App\Repository\FicheFraisRepository;
use App\Entity\FicheFrais;
use App\Entity\LigneFraisHorsForfait;


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
                        
                        $idv = $query->request->get('Visiteur');
                        
                        $ff = $this->getDoctrine()->getRepository(FicheFrais::class)->moisparVisiteur($idv);
                        
                         /*   $lignehff = $this->getDoctrine()->getRepository(\App\Repository\LigneFraisHorsForfaitRepository::class)->();
                         *
                         */
                        return $this->render('comptable/FicheFrais.html.twig' , [ 'visiteurs' => $lesVisiteur,'idvisiteurChoise' => $idv,'l' => true,'ff' => $ff,'moisChoise' => null]);
                    }
                    if($query->request->get('mois') != null && $query->request->get('mois') != "null"){
                       
                        $mois = $query->request->get('mois');
                        $idv = $query->request->get('visiteurchoise');
                        
                        if($mois != ""){

                            $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
                            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);
                            $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->LHFFwithMonthandIdv($mois,$idv);
                            $ff = $this->getDoctrine()->getRepository(FicheFrais::class)->moisparVisiteur($idv);
                            
                            
                            return $this->render('comptable/FicheFrais.html.twig' , [ 'visiteurs' => $lesVisiteur,'idvisiteurChoise' => $idv,'l' => true,'ff' => $ff,'moisChoise' => $mois, "ligneFF" => $ligneff , "ligneHFF" => $ligneHff , "FicheF" => $ficheF ]);
                            
                        }
                        
                    }
                    
                    
                        return $this->render('comptable/FicheFrais.html.twig' , [ 'visiteurs' => $lesVisiteur,'idvisiteurChoise' => $idv,'moisChoise' => null,'l' => false]);
                }



                return $this->render('comptable/FicheFrais.html.twig' , [ 'visiteurs' => $lesVisiteur, 'l' => false,'idvisiteurChoise' => $idv,'moisChoise' => null]);
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
    
    
    
    

