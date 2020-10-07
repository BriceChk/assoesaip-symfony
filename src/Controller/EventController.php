<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventOccurrence;
use App\Entity\ProjectMember;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @Route("/evenement/{url}", name="event")
     * @IsGranted("ROLE_USER")
     */
    public function index($url)
    {
        $em = $this->getDoctrine()->getRepository(Event::class);
        $event = $em->findOneBy(['url' => $url]);

        if ($event == null) {
            throw $this->createNotFoundException("Cet événement n'existe pas");
        }

        if (!$event->isPublished()) {
            $this->denyAccessUnlessGranted('ROLE_PROJECT_EDITOR');
        }

        $em = $this->getDoctrine()->getRepository(ProjectMember::class);
        $member = $em->findOne($event->getProject(), $event->getAuthor());

        $em = $this->getDoctrine()->getRepository(EventOccurrence::class);
        $occur = $em->findNextEventOccurrences($event);

        return $this->render('event.html.twig', [
            'event' => $event,
            'author' => $member,
            'occurrences' => $occur
        ]);
    }
}
