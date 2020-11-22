<?php

namespace App\Controller;

use App\Entity\EventCategory;
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
        $rep = $this->getDoctrine()->getRepository(EventCategory::class);
        $categs = $rep->findAll();

        return $this->render('calendar.html.twig', [
            "categs" => $categs
        ]);
    }
}
