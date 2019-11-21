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
use App\Entity\FicheFrais;
use App\Repository\FicheFraisRepository;
use App\Entity\Etat;
use App\Repository\EtatRepository;


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
                    $_SESSION['visiteur'] = $lesVisiteurs;
                  
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
        if(isset($_SESSION['login']) and isset($_SESSION['visiteur'])){
            if($_SESSION['login'] == true){
                
                $idv = $_SESSION['visiteur']->getId();
                $mois = null;
                $visiteur = $this->getDoctrine()->getRepository(Visiteur::class)->find($idv);
                
                if(date('j') > 15 ){
                    $mois = date("F", strtotime("+1 month", strtotime(date("F") . "1")) ); 
                }
                else{
                    $mois = date('F');
                }
                $monthyear= array($mois,date('Y'));
                $_SESSION['mois'] = $mois;

                $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
                
                if(sizeof($ligneff) < 1 ){
                    $fraisForfait = $this->getDoctrine()->getRepository(FraisForfait::class)->findAll();
                    
                    foreach($fraisForfait as $i){
                        $entityManager = $this->getDoctrine()->getManager();
                        $LFF = new LigneFraisForfait();
                        $LFF->setIdFraisForfait($i);
                        $LFF->setIdVisiteur($visiteur);
                        $LFF->setMois($mois);
                        $LFF->setQuantite(0);
                        
                        //Persitance de l'objet
                        $entityManager->persist($LFF);
                        // Exécution du INSERT INTO
                        $entityManager->flush();
                        
                        $LFF = null;
                    }    
                    $etat = $this->getDoctrine()->getRepository(Etat::class)->find(3);


                    $FicheFrais = new FicheFrais();
                    $FicheFrais->setDateModif(new \DateTime());
                    $FicheFrais->setIdVisiteur($visiteur);
                    $FicheFrais->setMois($mois);
                    $FicheFrais->setMontantValide(0);
                    $FicheFrais->setNbJustificatifs(0);
                    $FicheFrais->setIdEtat($etat);
                    $entityManager->persist($FicheFrais);
                    $entityManager->flush();
                        
                      
                }
                
                $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
                
                
                

                    
                
               
                
                
                
                
                
                return $this->render('visiteur/VueNouveauFrais.html.twig',['my' => $monthyear , 'ligneff' => $ligneff]);
            }
        }
        return $this->redirect('LoginVisiteur');
        
    
        
        
    }
    
    
     /**
     * @Route("/ModifierLigneForfait", name="ModifierLigneForfait")
     */
    public function ModifierLigneForfait(Request $query)
    {  
        $montantValider = 0;
        $entityManager = $this->getDoctrine()->getManager();
        if($query->isMethod('POST')){
            $mois = $_SESSION['mois'];
            $idv = $_SESSION['visiteur']->getId();
            
            $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);
            $li = new FicheFrais();
            
            foreach($ligneff as $l){                
                
                $quantite = $query->request->get($l->getIdFraisForfait()->getId());
                $montantValider = $montantValider + ($quantite * $l->getIdFraisForfait()->getMontant());
                $l->setQuantite(intval($quantite));
                $entityManager->merge($l);
                $entityManager->flush();

            } 
            $ficheF->setMontantValide($montantValider);
            $ficheF->setDateModif(new \DateTime());
            $entityManager->merge($ficheF);
            $entityManager->flush();
           
           
       }
        return $this->redirect('SaisirNouveauFrais');
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
