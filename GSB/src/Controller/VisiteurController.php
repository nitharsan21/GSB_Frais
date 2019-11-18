<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Visiteur;
use App\Repository\VisiteurRepository;
use App\Form\LoginVisiteurType;
use Symfony\Component\HttpFoundation\Session\Session;
use Psr\Log\LoggerInterface;
use App\Entity\LigneFraisForfait;
use App\Repository\LigneFraisForfaitRepository;
use App\Entity\FraisForfait;



class VisiteurController extends AbstractController
{
    /**
     * @Route("/visiteur", name="visiteur")
     */
    public function index()
    {
        return $this->render('visiteur/index.html.twig', [
            'controller_name' => 'VisiteurController',
        ]);
    }
    
    
    /**
     * @Route("/LoginVisiteur", name="LoginVisiteur")
     */
    public function LoginVisiteur(Request $query)
    {   
        $visiteur = new Visiteur();
        $summitt = false;
        $form = $this->createForm(LoginVisiteurType::class, $visiteur);
        
        $form->handleRequest($query);
        if ($form->isSubmitted() && $form->isValid()) {
                
            $login = $form['login']->getData();
            $password = $form['mdp']->getData();
            $lesVisiteurs = new Visiteur();
            $lesVisiteurs = $this->getDoctrine()->getRepository(Visiteur::class)->LoginVisiteur($login,$password);
            

            if($lesVisiteurs != null){
                    
                    $session = new Session();
                    $session->set('user_nom',$lesVisiteurs->getNom());
                    $session->set('user_prenom',$lesVisiteurs->getPrenom());
                    $session->set('user_id',$lesVisiteurs->getId());
                    $session->set('login',"Salut");
                    $_SESSION['login'] = true;
                    $_SESSION['user_id'] = $lesVisiteurs->getId();
                    return $this->redirect('CompteVisiteur');
            }
            $summitt = true;
        }
        return $this->render('visiteur/LoginVisiteur.html.twig',array('form'=>$form->createView(),'essie'=>$summitt,));
    }
    
    /**
     * @Route("/CompteVisiteur", name="CompteVisiteur")
     */
    public function CompteVisiteur()
    {   
        if(isset($_SESSION['login'])){
            if($_SESSION['login'] == true){
                return $this->render('visiteur/VueAccueilVisiteur.html.twig');
            }
        }
        return $this->redirect('LoginVisiteur');
    }
    
    
    /**
     * @Route("/deconnexionCompteVisiteur", name="deconnexionCompteVisiteur")
     */
    public function deconnexionCompteVisiteur()
    {   
        if(isset($_SESSION['login'])){
            if($_SESSION['login'] == true){
                $_SESSION['login'] = false;
                return $this->redirect('LoginVisiteur');
            }
        }
        return $this->redirect('LoginVisiteur');
    }
    
    /**
     * @Route("/SaisirNouveauFrais" , name="SaisirNouveauFrais")
     */
    public function SaisirNouveauFrais(){
        if(isset($_SESSION['login']) and isset($_SESSION['user_id'])){
            if($_SESSION['login'] == true){
                
                $idv = $_SESSION['user_id'];
                $mois = null;
                if(date('j') > 15 ){
                    $mois = date("F", strtotime("+1 month", strtotime(date("F") . "1")) ); 
                }
                else{
                    $mois = date('F');
                }
                $monthyear= array($mois,date('Y'));
                
                $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
              
                 
                if(sizeof($ligneff) < 1 ){
                    $fraisForfait = $this->getDoctrine()->getRepository(FraisForfait::class)->findAll();
                    
                    foreach($fraisForfait as $i){
                        $entityManager = $this->getDoctrine()->getManager();
                        $LFF = new LigneFraisForfait();
                        $LFF->setIdFraisForfait($i->getId());
                        $LFF->setIdVisiteur($idv);
                        $LFF->setMois($mois);
                        $LFF->setQuantite(0);
                        
                        //Persitance de l'objet
                        $entityManager->persist($LFF);
                        // Exécution du INSERT INTO
                        $entityManager->flush();
                    
                    }
                        
                }

                    
                
               
                
                
                
                
                
                return $this->render('visiteur/VueNouveauFrais.html.twig',['my' => $monthyear]);
            }
        }
        return $this->redirect('LoginVisiteur');
        
    
        
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
