<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CafetController extends AbstractController
{
    /**
     * @Route("/cafet", name="cafet")
     */
    public function index(): Response
    {
        return $this->render('cafet/index.html.twig', [
            'controller_name' => 'CafetController',
        ]);
    }
}
