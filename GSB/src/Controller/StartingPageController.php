<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StartingPageController extends AbstractController
{
    /**
     * @Route("/starting/page", name="starting_page")
     */
    public function index()
    {
        return $this->render('starting_page/index.html.twig');
    }
}
