<?php

namespace App\Controller;

use App\Entity\AssoEsaipSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends AbstractController
{
    /**
     * @Route("/maintenance", name="maintenance")
     */
    public function index(): Response
    {
        $setRep = $this->getDoctrine()->getRepository(AssoEsaipSettings::class);

        return $this->render('maintenance.html.twig', [
            'message' => $setRep->getMaintenanceModeMessage()
        ]);
    }
}
