<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LoginOfficeController extends AbstractController
{
    /**
     * @Route("/login-office", name="login_office")
     */
    public function index()
    {
        return $this->render('login.html.twig');
    }
}
