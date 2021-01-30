<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicPageController extends AbstractController
{
    /**
     * @Route("/public", name="public_page", priority="100")
     */
    public function index(): Response
    {
        return $this->render('public.html.twig');
    }
}
