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
use App\Entity\LigneFraisHorsForfait;
use App\Form\LHFFType;

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
                    $_SESSION = array();
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
                $_SESSION = array();
                return $this->redirect('LoginVisiteur');
            }
        }
        return $this->redirect('LoginVisiteur');
    }
    
    /**
     * @Route("/SaisirNouveauFrais" , name="SaisirNouveauFrais")
     */
    public function SaisirNouveauFrais(Request $query){
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
                
                $ligneHorsForFait = new LigneFraisHorsForfait();
                $form = $this->createForm(LHFFType::class, $ligneHorsForFait); 
                
                $form->handleRequest($query);
                if ($form->isSubmitted() && $form->isValid()) {
                    $ligneHorsForFait->setMois($mois);
                    $ligneHorsForFait->setIdVisiteur($visiteur);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($ligneHorsForFait);
                    $entityManager->flush();
                   
                    $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);
                    $ficheF->setMontantValide($ficheF->getMontantValide() + $ligneHorsForFait->getMontant());
                    $ficheF->setDateModif(new \DateTime());
                    $entityManager->merge($ficheF);
                    $entityManager->flush();
                    
                    
                    return $this->redirect('SaisirNouveauFrais');        
                }
                
                
                $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->LHFFwithMonthandIdv($mois,$idv);
                $LHFFexiste = false;
                
                if(sizeof($ligneHff)>0){
                    $LHFFexiste = true;
                }
                
                $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);

                return $this->render('visiteur/VueNouveauFrais.html.twig',['my' => $monthyear , 'ligneff' => $ligneff, 'ligneHFF' => $ligneHff,
                    'LHFFexisite' => $LHFFexiste, 'form'=>$form->createView() , 'FicheFrais' => $ficheF]);
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
            $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->LHFFwithMonthandIdv($mois,$idv);
            $li = new FicheFrais();
            
            foreach($ligneff as $l){                
                
                $quantite = $query->request->get($l->getIdFraisForfait()->getId());
                $montantValider = $montantValider + ($quantite * $l->getIdFraisForfait()->getMontant());
                $l->setQuantite(intval($quantite));
                $entityManager->merge($l);
                $entityManager->flush();

            }
            foreach($ligneHff as $h){
                $montantValider = $montantValider + $h->getMontant();
            }
            
            $ficheF->setMontantValide($montantValider);
            $ficheF->setDateModif(new \DateTime());
            $entityManager->merge($ficheF);
            $entityManager->flush();
           
           
       }
        return $this->redirect('SaisirNouveauFrais');
    }
    
    
     /**
     * @Route("/Supperimer/{idLHFF}", name="SupperimerLHFF")
     */
    function SupperimerLHFF($idLHFF){
        
        $entityManager = $this->getDoctrine()->getManager();     
        $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->findOneBy(['id' => $idLHFF]);
        
        if($ligneHff != null ){
            
            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($ligneHff->getMois(),$ligneHff->getIdVisiteur()->getId());
        
            $ficheF->setMontantValide($ficheF->getMontantValide() - $ligneHff->getMontant());
            $entityManager->merge($ficheF);
            $entityManager->remove($ligneHff);
            $entityManager->flush();
        }
              
        return $this->redirect('/SaisirNouveauFrais');
    

    }
    
    
     
     /**
     * @Route("/SetNBJustificatifs", name="SetNBJustificatifs")
     */
    function SetNBJustificatifs(Request $query){
        $entityManager = $this->getDoctrine()->getManager(); 
        if($query->isMethod('POST')){
            $mois = $_SESSION['mois'];
            $idv = $_SESSION['visiteur']->getId();
            
            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);
            
            $ficheF->setNbJustificatifs($query->request->get("nbJ"));
            $ficheF->setDateModif(new \DateTime());
            $entityManager->merge($ficheF);
            $entityManager->flush();
            
            
            
        }
        
        return $this->redirect('/SaisirNouveauFrais');
    }
    
    
    
    /**
     * @Route("/ConsultFrais", name="ConsultFrais")
     */
    function ConsultFrais(Request $query){
        
        
       
        if(isset($_SESSION['login']) and isset($_SESSION['visiteur'])){
            if($_SESSION['login'] == true){
                $idv = $_SESSION['visiteur']->getId();      
                $mois = $this->getDoctrine()->getRepository(FicheFrais::class)->moisparVisiteur($idv);
                $MoisChoise = null;
                $visiteur = $this->getDoctrine()->getRepository(Visiteur::class)->find($idv);
                
                if($query->isMethod('POST')){
                    
                    $MoisChoise = $query->request->get("mois");
                    $_SESSION['MoisChoise'] = $MoisChoise;
                    
                    
                    if($MoisChoise != "" or $query->request->get("monthid") != ""){
                        
                        $ligneHorsForFait = new LigneFraisHorsForfait();
                        $form = $this->createForm(LHFFType::class, $ligneHorsForFait);
                        
                        $form->handleRequest($query);
                        if ($form->isSubmitted() && $form->isValid()) {
                            $ligneHorsForFait->setMois($query->request->get("monthid"));
                            $ligneHorsForFait->setIdVisiteur($visiteur);
                            $entityManager = $this->getDoctrine()->getManager();
                            $entityManager->persist($ligneHorsForFait);
                            $entityManager->flush();

                            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($query->request->get("monthid"),$idv);
                            $ficheF->setMontantValide($ficheF->getMontantValide() + $ligneHorsForFait->getMontant());
                            $ficheF->setDateModif(new \DateTime());
                            $entityManager->merge($ficheF);
                            $entityManager->flush();
                           
                            $_SESSION['MoisChoise'] = $query->request->get("monthid");

                            return $this->redirect('ConsultFrais');

                            }
                        
                        $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$MoisChoise);
                        $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($MoisChoise,$idv);
                        $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->LHFFwithMonthandIdv($MoisChoise,$idv);



                        return $this->render('visiteur/VueConsultFrais.html.twig',['mois' =>$mois, "MoisChoise" => $MoisChoise , "ligneFF" => $ligneff , "ligneHFF" => $ligneHff , "FicheF" => $ficheF , "form" =>$form->createView() ,]);
                    
                    }
                    else{
                        return $this->render('visiteur/VueConsultFrais.html.twig',['mois' =>$mois,"MoisChoise" => $MoisChoise]);
                    }
        
                        
                    }
                
                /**if($query->isMethod('POST')){
                    
                    $ligneHorsForFait = new LigneFraisHorsForfait();
                    $form = $this->createForm(LHFFType::class, $ligneHorsForFait);
                    
                    $form->handleRequest($query);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $ligneHorsForFait->setMois($_SESSION['MoisChoise']);
                        $ligneHorsForFait->setIdVisiteur($visiteur);
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($ligneHorsForFait);
                        $entityManager->flush();

                        $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($_SESSION['MoisChoise'],$idv);
                        $ficheF->setMontantValide($ficheF->getMontantValide() + $ligneHorsForFait->getMontant());
                        $ficheF->setDateModif(new \DateTime());
                        $entityManager->merge($ficheF);
                        $entityManager->flush();


                        return $this->redirect('ConsultFrais');       

                    }
                }*/
                
                if(isset($_SESSION['MoisChoise'])){
                    if($_SESSION['MoisChoise'] != null){

                        $MoisChoise = $_SESSION['MoisChoise'];

                        if($MoisChoise != ""){

                            $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$MoisChoise);
                            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($MoisChoise,$idv);
                            $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->LHFFwithMonthandIdv($MoisChoise,$idv);

                            $ligneHorsForFait = new LigneFraisHorsForfait();
                            $form = $this->createForm(LHFFType::class, $ligneHorsForFait);   

                            return $this->render('visiteur/VueConsultFrais.html.twig',['mois' =>$mois, "MoisChoise" => $MoisChoise , "ligneFF" => $ligneff , "ligneHFF" => $ligneHff , "FicheF" => $ficheF , "form" =>$form->createView() ,]);
                        }
                        $MoisChoise = null;
                        $_SESSION['MoisChoise'] = null;
                        return $this->render('visiteur/VueConsultFrais.html.twig',['mois' =>$mois,"MoisChoise" => $MoisChoise]);

                    }
                
                }
            
                
                
            
                return $this->render('visiteur/VueConsultFrais.html.twig',['mois' =>$mois,"MoisChoise" => $MoisChoise]);
            }
            
        }
         return $this->redirect('LoginVisiteur');
    }
    
    
     
     /**
     * @Route("/SetNBJustificatifsDansConsulter", name="SetNBJustificatifsDansConsulter")
     */
    function SetNBJustificatifsDansConsulter(Request $query){
        $entityManager = $this->getDoctrine()->getManager(); 
        if($query->isMethod('POST')){
            $mois = $_SESSION['MoisChoise'];
            $idv = $_SESSION['visiteur']->getId();
            $query->attributes->set('mois', $mois);
            
            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);
            
            $ficheF->setNbJustificatifs($query->request->get("nbJ"));
            $ficheF->setDateModif(new \DateTime());
            $entityManager->merge($ficheF);
            $entityManager->flush();
            
            
            
        }
        
        return $this->redirect('ConsultFrais');
    }
    
    
    /**
     * @Route("/ModifierLigneForfaitConsulter", name="ModifierLigneForfaitConsulter")
     */
    public function ModifierLigneForfaitConsulter(Request $query)
    {  
        $montantValider = 0;
        $entityManager = $this->getDoctrine()->getManager();
        if($query->isMethod('POST')){
            $mois = $_SESSION['MoisChoise'];
            $idv = $_SESSION['visiteur']->getId();
            
            $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);
            $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->LHFFwithMonthandIdv($mois,$idv);
            $li = new FicheFrais();
            
            foreach($ligneff as $l){                
                
                $quantite = $query->request->get($l->getIdFraisForfait()->getId());
                $montantValider = $montantValider + ($quantite * $l->getIdFraisForfait()->getMontant());
                $l->setQuantite(intval($quantite));
                $entityManager->merge($l);
                $entityManager->flush();

            }
            foreach($ligneHff as $h){
                $montantValider = $montantValider + $h->getMontant();
            }
            
            $ficheF->setMontantValide($montantValider);
            $ficheF->setDateModif(new \DateTime());
            $entityManager->merge($ficheF);
            $entityManager->flush();
           
           
       }
        return $this->redirect('ConsultFrais');
    }
    
    
    /**
    * @Route("/SupperimerC/{idLHFF}", name="SupperimerLHFFConsult")
    */
    function SupperimerLHFFConsult($idLHFF){
        
        $entityManager = $this->getDoctrine()->getManager();     
        $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->findOneBy(['id' => $idLHFF]);
        
        if($ligneHff != null ){
            
            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($ligneHff->getMois(),$ligneHff->getIdVisiteur()->getId());
        
            $ficheF->setMontantValide($ficheF->getMontantValide() - $ligneHff->getMontant());
            $entityManager->merge($ficheF);
            $entityManager->remove($ligneHff);
            $entityManager->flush();
        }
              
        return $this->redirect('/ConsultFrais');
    

    }
    
    
    /**
     * @Route("/SetMontantFFHFDansConsulter", name="SetMontantFFHFDansConsulter")
     */
    function SetMontantFFHFDansConsulter(Request $query){
        
        $montantValider = 0;
        $entityManager = $this->getDoctrine()->getManager(); 
        if($query->isMethod('POST')){
            $mois = $_SESSION['MoisChoise'];
            $idv = $_SESSION['visiteur']->getId();
            $query->attributes->set('mois', $mois);
            
            $LigneFHFF = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->find($query->request->get("idFFHF"));
            
            $LigneFHFF->setMontant($query->request->get("montantFFHF"));
            
            $entityManager->merge($LigneFHFF);
            $entityManager->flush();
            

            $ligneff = $this->getDoctrine()->getRepository(LigneFraisForfait::class)->getLFFwithIDVisiteurAndMonth($idv,$mois);
            $ficheF = $this->getDoctrine()->getRepository(FicheFrais::class)->ficheforfaitwithMonthandIdv($mois,$idv);
            $ligneHff = $this->getDoctrine()->getRepository(LigneFraisHorsForfait::class)->LHFFwithMonthandIdv($mois,$idv);
            $li = new FicheFrais();
            
            foreach($ligneff as $l){                
                
                $quantite = $l->getQuantite();
                $montantValider = $montantValider + ($quantite * $l->getIdFraisForfait()->getMontant());

            }
            foreach($ligneHff as $h){
                $montantValider = $montantValider + $h->getMontant();
            }
            
            $ficheF->setMontantValide($montantValider);
            $ficheF->setDateModif(new \DateTime());
            $entityManager->merge($ficheF);
            $entityManager->flush();
            
            
            
        }
        
        return $this->redirect('ConsultFrais');
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
