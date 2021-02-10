<?php

namespace App\Controller;

use App\Entity\AENews;
use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\Event;
use App\Entity\EventCategory;
use App\Entity\Project;
use App\Entity\ProjectCategory;
use App\Entity\RessourcePage;
use App\Entity\User;
use App\Utils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EspaceAssosController extends AbstractController
{

    /*
     * ============================
     * RESSOURCES (first because of the route priority)
     * ============================
     */

    /**
     * @Route("/espace-assos/ressources", name="espace_assos_ressources_summary")
     * @IsGranted("ROLE_USER")
     */
    public function summary() {
        $rep = $this->getDoctrine()->getRepository(RessourcePage::class);

        /** @var RessourcePage[] $pages */
        $pages = $rep->findBy([], ['orderPosition' => 'ASC']);

        return $this->render('espace_assos/ressources_summary.html.twig', [
            'pages' => $pages
        ]);
    }

    /**
     * @Route("/espace-assos/ressources/{url}", name="espace_assos_ressource")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return Response
     */
    public function ressourcePage($url) {
        $rep = $this->getDoctrine()->getRepository(RessourcePage::class);

        /** @var RessourcePage $page */
        $page = $rep->findOneBy(['url' => $url]);

        if ($page == null) {
            throw $this->createNotFoundException("Cette page n'existe pas");
        }

        return $this->render('espace_assos/ressource.html.twig', [
            'page' => $page
        ]);
    }

    /*
     * ============================
     * HOME
     * ============================
     */

    /**
     * @Route("/espace-assos", name="espace_assos_no_project")
     * @IsGranted("ROLE_USER")
     */
    public function indexRedirect()
    {
        $user = $this->getGoodUser();
        $projectsList = $this->isGranted('ROLE_ADMIN') ? $this->getAllProjects() : $user->getProjectsIfAdmin();

        // The user has no project
        if (count($projectsList) == 0) {
            return $this->render('espace_assos/espace_assos_no_project.html.twig', ['noProject' => true]);
        }

        $rep = $this->getDoctrine()->getRepository(Project::class);
        $firstProject = $rep->find($projectsList[0]->getId());

        // Else, retrieve selected project or select the first one in the list
        $projectUrl = $this->get('session')->get('selectedProject', $firstProject->getUrl());

        // Just check if the user is still a project admin or if the project exists. If not, just take the first one
        if ($projectUrl != $firstProject->getUrl()) {
            $proj = $rep->findOneByUrl($projectUrl);
            if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
                $projectUrl = $firstProject->getUrl();
            }
        }

        return $this->redirectToRoute('espace_assos', ['url' => $projectUrl]);
    }

    /**
     * @Route("/espace-assos/{url}", name="espace_assos")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function index($url)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        $aenewsRepo = $this->getDoctrine()->getRepository(AENews::class);
        $aenews = $aenewsRepo->findBy(['published' => true]);

        $this->get('session')->set('selectedProject', $url);

        return $this->render('espace_assos/espace_assos.html.twig', [
            'project' => $proj,
            'aenews' => $aenews
        ]);
    }

    /*
     * ============================
     * INFOS
     * ============================
     */

    /**
     * @Route("/espace-assos/{url}/infos", name="espace_assos_infos")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function projectInfos($url)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categories = $rep->findAll();
        $assRep = $this->getDoctrine()->getRepository(Project::class);
        $assos = $assRep->findBy(['type' => 'Association', 'campus' => $this->getGoodUser()->getCampus()]);

        return $this->render('espace_assos/infos.html.twig', [
            'project' => $proj,
            'categories' => $categories,
            'assos' => $assos
        ]);
    }

    /*
     * ============================
     * PAGES
     * ============================
     */

    /**
     * @Route("/espace-assos/{url}/pages", name="espace_assos_pages")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function projectPages($url)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        return $this->render('espace_assos/pages.html.twig', [
            'project' => $proj
        ]);
    }


    /*
     * ============================
     * ARTICLES
     * ============================
     */

    /**
     * @Route("/espace-assos/{url}/articles", name="espace_assos_articles_list")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function articlesList($url)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        return $this->render('espace_assos/articles_list.html.twig', [
            'project' => $proj
        ]);
    }

    /**
     * @Route("/espace-assos/{url}/articles/nouveau", name="espace_assos_articles_write")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function articleNew($url)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        $rep = $this->getDoctrine()->getRepository(ArticleCategory::class);
        $categories = $rep->findAll();

        return $this->render('espace_assos/articles_edit.html.twig', [
            'categories' => $categories,
            'articleId' => -1,
            'projectId' => $proj->getId()
        ]);
    }

    /**
     * @Route("/espace-assos/{url}/articles/{id}", name="espace_assos_articles_edit")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @param $id
     * @return RedirectResponse|Response
     */
    public function articleWrite($url, $id)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        // Check article
        $em = $this->getDoctrine()->getRepository(Article::class);
        $article = $em->find($id);
        if ($article == null || $article->getProject() != $proj) {
            $this->createNotFoundException('Article introuvable');
        }

        $rep = $this->getDoctrine()->getRepository(ArticleCategory::class);
        $categories = $rep->findAll();

        return $this->render('espace_assos/articles_edit.html.twig', [
            'article' => $article,
            'categories' => $categories,
            'articleId' => $article->getId(),
            'projectId' => $proj->getId()
        ]);
    }

    /*
     * ============================
     * EVENTS
     * ============================
     */

    /**
     * @Route("/espace-assos/{url}/evenements", name="espace_assos_events_list")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function eventsList($url)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        return $this->render('espace_assos/events_list.html.twig', [
            'project' => $proj
        ]);
    }

    /**
     * @Route("/espace-assos/{url}/evenements/nouveau", name="espace_assos_events_write")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function eventNew($url)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        $rep = $this->getDoctrine()->getRepository(EventCategory::class);
        $categories = $rep->findAll();

        return $this->render('espace_assos/events_edit.html.twig', [
            'categories' => $categories,
            'eventId' => -1,
            'projectId' => $proj->getId()
        ]);
    }

    /**
     * @Route("/espace-assos/{url}/evenements/{id}", name="espace_assos_events_edit")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @param $id
     * @return RedirectResponse|Response
     */
    public function eventWrite($url, $id)
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        // Check event
        $em = $this->getDoctrine()->getRepository(Event::class);
        $event = $em->find($id);
        if ($event == null || $event->getProject() !== $proj) {
            $this->createNotFoundException('Événement introuvable');
        }

        $rep = $this->getDoctrine()->getRepository(EventCategory::class);
        $categories = $rep->findAll();

        $time = "";
        if ($event->getOccurrencesCount() > 1 && !$event->isAllDay()) {
            $hours = $event->getDuration() / 60;
            $rhours = floor($hours);
            $minutes = ($hours - $rhours) * 60;
            $rminutes = round($minutes);
            $time = Utils::twoDigits($rhours) . ':' . Utils::twoDigits($rminutes);
        }

        return $this->render('espace_assos/events_edit.html.twig', [
            'event' => $event,
            'categories' => $categories,
            'eventId' => $event->getId(),
            'projectId' => $proj->getId(),
            'time' => $time
        ]);
    }

    /*
     * ============================
     * ROOM BOOKING
     * ============================
     */

    /**
     * @Route("/espace-assos/{url}/reservation-salle", name="espace_assos_room_booking")
     * @IsGranted("ROLE_USER")
     * @param $url
     * @return RedirectResponse|Response
     */
    public function roomBook($url) {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $proj = $rep->findOneByUrl($url);

        if ($proj == null || !$this->isGranted('PROJECT_ADMIN', $proj)) {
            return $this->redirectToRoute('espace_assos_no_project');
        }

        return $this->render('espace_assos/room_booking.html.twig', [
            'roombooks' => $proj->getRoomBooks(),
            'projectId' => $proj->getId()
        ]);
    }

    /*
     * ============================
     * OTHER STUFF
     * ============================
     */

    // Render the <option>s for the list of projects
    public function renderProjectList()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $rep = $this->getDoctrine()->getRepository(Project::class);
            $projects = $rep->findAll();
        } else {
            $projects = $this->getGoodUser()->getProjectsIfAdmin();
        }

        return $this->render('espace_assos/_project_list.html.twig', [
            'projects' => $projects
        ]);
    }

    public function renderRessourcePageList($currentUrl) {
        $rep = $this->getDoctrine()->getRepository(RessourcePage::class);
        $ressources = $rep->findBy(['published' => true], ['orderPosition' => 'ASC']);

        return $this->render('espace_assos/_ressource_page_list.html.twig', [
            'ressources' => $ressources,
            'url' => $currentUrl
        ]);
    }

    private function getGoodUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();
        return $user;
    }

    /**
     * @return Project[]
     */
    private function getAllProjects()
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        return $rep->findAll();
    }
}
