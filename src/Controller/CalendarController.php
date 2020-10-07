<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    /**
     * @Route("/calendrier", name="calendar")
     * @IsGranted("ROLE_USER")
     */
    public function index()
    {
        return $this->render('calendar.html.twig');
    }
}
