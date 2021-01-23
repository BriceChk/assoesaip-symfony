<?php

namespace App\Controller;

use App\Entity\CafetItem;
use DateInterval;
use DateTime;
use IntlDateFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CafetController extends AbstractController
{
    /**
     * @Route("/cafet", name="cafet")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function index(): Response
    {
        $rep = $this->getDoctrine()->getRepository(CafetItem::class);
        $items = $rep->findAll();

        $date = new DateTime('now');

        // Add one day to the date if time is past 12h45
        $testDate = new DateTime('now');
        $testDate = $testDate->setTime(12, 45, 0);
        if ($date > $testDate) {
            $date = $date->add(new DateInterval('P1D'));
        }

        $repas = array();
        $desserts = array();
        $boissons = array();

        while (count($repas) == 0) {
            $f = $date->format('N');
            $repas = array();
            $desserts = array();
            $boissons = array();
            foreach ($items as $i) {
                if (strpos($i->getDay(), $f) !== false) {
                    switch ($i->getType()) {
                        case "Repas":
                            $repas[] = $i;
                            break;
                        case "Dessert":
                            $desserts[] = $i;
                            break;
                        case "Boisson":
                            $boissons[] = $i;
                            break;
                    }
                }
            }
            if (count($repas) == 0) {
                $date = $date->add(new DateInterval('P1D'));
            }
        }

        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE);
        $formatter->setPattern('EEEE');

        return $this->render('cafet.html.twig', [
            'repas' => $repas,
            'boissons' => $boissons,
            'desserts' => $desserts,
            'day' => $formatter->format($date),
        ]);
    }
}
