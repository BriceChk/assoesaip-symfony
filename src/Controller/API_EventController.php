<?php


namespace App\Controller;


use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\EventOccurrence;
use App\Entity\News;
use App\Entity\Project;
use App\Entity\ProjectCategory;
use App\Entity\User;
use App\Utils;
use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use HTMLPurifier;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//TODO Image upload + Discord integration

class API_EventController extends AbstractFOSRestController {
    /**
     * Get an Event from its ID.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the requested Event",
     *     @OA\JsonContent(ref=@Model(type=Event::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Event doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Event unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Event")
     * @Rest\Get(
     *     path = "/api/event/{id}",
     *     name = "api_event_show",
     *     requirements = { "id"="\d+" }
     * )
     * @View()
     * @IsGranted("ROLE_USER")
     * @param Integer $id The Event ID
     * @return Event|Response
     */
    public function showEvent(int $id) {
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $event = $eventRepo->find($id);

        if ($event == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun événement n'a été trouvée avec cet ID."));
            return $response;
        }

        return $event;
    }

    /**
     * Create a new Event. The user must be a Project admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created Event",
     *     @OA\JsonContent(ref=@Model(type=Event::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Project unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="The Event as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=Event::class))
     * )
     * @OA\Tag(name="Event")
     * @Rest\Put(
     *     path = "/api/project/{id}/events",
     *     name = "api_project_event_create",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @View(statusCode=201)
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param HTMLPurifier $purifier
     * @return Event|\FOS\RestBundle\View\View|Response
     */
    public function newEvent($id, Request $request, ValidatorInterface $validator, HTMLPurifier $purifier) {
        $response = new Response();
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $project = $rep->find($id);
        if ($project == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun projet trouvé avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $project)) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $event = new Event();
        $json = $request->request->all();

        $cat = $this->getDoctrine()->getRepository(EventCategory::class)->find($json['category']);
        if ($cat == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie trouvée avec cet ID."));
            return $response;
        }
        $event->setCategory($cat);

        $event->setTitle($json['title']);
        $event->setAuthor($this->getUser());
        $event->setProject($project);
        $event->setAbstract(trim($json['abstract']));
        $event->setHtml($purifier->purify($json['html']));
        $event->setDateCreated(new DateTime('now'));
        $event->setDateEdited(new DateTime('now'));
        $event->setPublished(false);
        $event->setPrivate($json['private']);
        $event->setAllDay($json['all_day']);
        $event->setIntervalCount($json['interval_count']);
        $event->setIntervalType($json['interval_type']);
        $event->setDuration($json['duration']);
        $event->setOccurrencesCount($json['occurrences_count']);
        $event->setDaysOfWeek($json['days_of_week']);
        try {
            $event->setDateStart(new DateTime($json['date_start']));
            if (array_key_exists('date_end', $json)) {
                $event->setDateEnd(new DateTime($json['date_end']));
            }
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Format de date invalide."));
            return $response;
        }

        $errors = $validator->validate($event);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($event);
        $em->flush();

        try {
            foreach ($json['occurrences'] as $occ) {
                $event->addOccurrence(new EventOccurrence(new DateTime($occ)));
            }
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Format de date d'occurrence invalide."));
            return $response;
        }

        $em->persist($event);
        $em->flush();

        return $event;
    }

    /**
     * Update an Event. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested Event has been modified",
     *     @OA\JsonContent(ref=@Model(type=Event::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Event doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Event unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="The Event JSON object",
     *     @OA\JsonContent(ref=@Model(type=Event::class))
     * )
     * @OA\Tag(name="Event")
     * @Rest\Post(
     *     path = "/api/event/{id}",
     *     name = "api_event_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @View()
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param HTMLPurifier $purifier
     * @param SluggerInterface $slugger
     * @return Event|\FOS\RestBundle\View\View|object|Response
     */
    public function updateEvent($id, Request $request, ValidatorInterface $validator, HTMLPurifier $purifier, SluggerInterface $slugger) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(Event::class);
        $event = $rep->find($id);

        if ($event == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun événement trouvé avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $event->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $json = $request->request->all();

        $cat = $this->getDoctrine()->getRepository(EventCategory::class)->find($json['category']);
        if ($cat == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie trouvée avec cet ID."));
            return $response;
        }
        $event->setCategory($cat);

        $event->setTitle($json['title']);
        $event->setAbstract(trim($json['abstract']));
        $event->setHtml($purifier->purify($json['html']));
        $event->setDateEdited(new DateTime('now'));
        $event->setAllDay($json['all_day']);
        $event->setIntervalCount($json['interval_count']);
        $event->setIntervalType($json['interval_type']);
        $event->setDuration($json['duration']);
        $event->setOccurrencesCount($json['occurrences_count']);
        $event->setDaysOfWeek($json['days_of_week']);
        try {
            $event->setDateStart(new DateTime($json['date_start']));
            if (array_key_exists('date_end', $json)) {
                $event->setDateEnd(new DateTime($json['date_end']));
            }
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Format de date invalide."));
            return $response;
        }

        $publishNews = false;

        if (!$json['published']) {
            $event->setUrl(''); // L'event n'est pas publié : on enlève l'url
        } elseif ($event->isPrivate() && !$json['private']) {
            $publishNews = true; // L'event est publié et passe de privé à public : on publie la news
        }

        $event->setPrivate($json['private']);

        if (!$event->isPublished() && $json['published']) {
            // L'event passe de brouillon à publié : on crée l'url
            $event->setAuthor($this->getUser());
            $event->setDatePublished(new DateTime('now'));

            $slug = $slugger->slug($json['title']);
            $test = $rep->findBy(['url' => $slug]);
            while (count($test) != 0 && $test == $event->getProject()) {
                $slug .= '-1';
                $test = $rep->findBy(['url' => $slug]);
            }

            $event->setUrl($slug);

            if (!$event->isPrivate()) {
                $publishNews = true; // Si il n'est pas privé, on publie la news
            }
        }
        $event->setPublished($json['published']);

        $em = $this->getDoctrine()->getManager();

        // On enlève l'éventuelle news de l'event si l'event n'est pas publié ou en privé
        if (!$event->isPublished() || $event->isPrivate()) {
            $nRep = $this->getDoctrine()->getRepository(News::class);
            $oldNews = $nRep->findOneBy(['event' => $event]);
            if ($oldNews != null) {
                $em->remove($oldNews);
            }
        }

        $errors = $validator->validate($event);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($event);

        if ($publishNews) {
            $news = new News();
            $news->setEvent($event);
            $news->setDatePublished($event->getDatePublished());
            $news->setProject($event->getProject());
            $em->persist($news);
        }

        // Remove old occurrences and add new ones
        foreach ($event->getOccurrences() as $occ) {
            $event->removeOccurrence($occ);
        }

        try {
            foreach ($json['occurrences'] as $occ) {
                $event->addOccurrence(new EventOccurrence(new DateTime($occ)));
            }
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Format de date d'occurrence invalide."));
            return $response;
        }

        $em->persist($event);
        $em->flush();

        return $event;
    }

    /**
     * Delete an Event. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested Event has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Event doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Event unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Event")
     * @Rest\Delete(
     *     path = "/api/event/{id}",
     *     name = "api_event_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return Response
     */
    public function deleteEvent($id) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(Event::class);
        $event = $rep->find($id);

        if ($event == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun événement trouvé avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $event->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();

        $response->setContent(Utils::jsonMsg("L'événement a été supprimé."));
        return $response;
    }

    /**
     * Get Events in the FullCalendar data format.
     * https://fullcalendar.io/docs/event-object
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the array of FullCalendar Events",
     * )
     * @OA\Parameter (
     *     name = "start",
     *     in="query",
     *     description="The start date",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter (
     *     name = "end",
     *     in="query",
     *     description="The end date",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Event")
     * @Rest\Get(
     *     path = "/api/event/fullcalendar",
     *     name = "api_event_show_fc"
     * )
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return \FOS\RestBundle\View\View|Response
     */
    public function showFullCalEvents(Request $request) {
        $start = explode(' ', $request->query->get('start'))[0];
        $end = explode(' ', $request->query->get('end'))[0];

        $rep = $this->getDoctrine()->getRepository(EventOccurrence::class);

        try {
            $startDate = new DateTime($start);
            $endDate = new DateTime($end);
        } catch (\Exception $e) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Date invalide"));
            return $response;
        }

        $result = $rep->findBetweenDates($startDate, $endDate);
        $events = array();

        $admin = $this->isGranted("ROLE_ADMIN");
        $editor = $this->isGranted("ROLE_PROJECT_EDITOR");
        /** @var User $user */
        $user = $this->getUser();

        foreach ($result as $r) {
            $e = $r->getEvent();

            if ((!$admin && $e->getCampus() != $user->getCampus()) || (!$editor && !$e->isPublished())) {
                continue;
            }

            $events[] = $this->makeFCEvent($r, $e);
        }

        return $this->view($events);
    }

    /**
     * Get a Project's Events in the FullCalendar data format.
     * https://fullcalendar.io/docs/event-object
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the array of FullCalendar Events",
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Project unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter (
     *     name = "start",
     *     in="query",
     *     description="The start date",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter (
     *     name = "end",
     *     in="query",
     *     description="The end date",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Event")
     * @Rest\Get(
     *     path = "/api/project/{id}/events/fullcalendar",
     *     name = "api_event_show_project_fc"
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @return \FOS\RestBundle\View\View|Response
     */
    public function showProjectFullCalEvents($id, Request $request) {
        $response = new Response();
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $project = $rep->find($id);
        if ($project == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun projet trouvé avec cet ID."));
            return $response;
        }

        $start = explode(' ', $request->query->get('start'))[0];
        $end = explode(' ', $request->query->get('end'))[0];

        $rep = $this->getDoctrine()->getRepository(EventOccurrence::class);

        try {
            $startDate = new DateTime($start);
            $endDate = new DateTime($end);
        } catch (\Exception $e) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Date invalide"));
            return $response;
        }

        $result = $rep->findBetweenDates($startDate, $endDate);
        $events = array();

        $editor = $this->isGranted("PROJECT_ADMIN", $project);

        foreach ($result as $r) {
            $e = $r->getEvent();

            if ($e->getProject() != $project || (!$editor && !$e->isPublished())) {
                continue;
            }

            $events[] = $this->makeFCEvent($r, $e);
        }

        return $this->view($events);
    }

    /**
     * Get a ProjectCategory's Events in the FullCalendar data format.
     * https://fullcalendar.io/docs/event-object
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the array of FullCalendar Events",
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectCategory unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter (
     *     name = "start",
     *     in="query",
     *     description="The start date",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter (
     *     name = "end",
     *     in="query",
     *     description="The end date",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="Event")
     * @Rest\Get(
     *     path = "/api/project/category/{id}/events/fullcalendar",
     *     name = "api_event_show_category_fc"
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @return \FOS\RestBundle\View\View|Response
     */
    public function showCategFullCalEvents($id, Request $request) {
        $response = new Response();
        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categ = $rep->find($id);
        if ($categ == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie trouvée avec cet ID."));
            return $response;
        }

        $start = explode(' ', $request->query->get('start'))[0];
        $end = explode(' ', $request->query->get('end'))[0];

        $rep = $this->getDoctrine()->getRepository(EventOccurrence::class);

        try {
            $startDate = new DateTime($start);
            $endDate = new DateTime($end);
        } catch (\Exception $e) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Date invalide"));
            return $response;
        }

        $result = $rep->findBetweenDates($startDate, $endDate);
        $events = array();

        foreach ($result as $r) {
            $e = $r->getEvent();

            if ($e->getProject()->getCategory() != $categ) {
                continue;
            }

            $events[] = $this->makeFCEvent($r, $e);
        }

        return $this->view($events);
    }

    private function makeFCEvent($occ, $event): array {
        $a = array(
            "title" => $event->getTitle(),
            "allDay" => $event->isAllDay(),
            "url" => '/evenement/' . $event->getUrl(),
            "description" => $event->getAbstract(),
            "backgroundColor" => $event->getCategory()->getColor(),
            "borderColor" => $event->getCategory()->getColor(),
            "start" => $occ->getDate()->format('Y-m-d\TH:m:00')
        );

        if ($event->getOccurrencesCount() == 1) {
            $a['end'] = $event->getDateEnd()->format('Y-m-d\TH:m:00');
        } else {
            $a['duration'] = array("minutes" => $event->getDuration());
        }

        if (!$event->isPublished()) {
            $a['backgroundColor'] = '#fca503';
            $a['borderColor'] = '#fca503';
        }

        return $a;
    }
}