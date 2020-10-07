<?php

namespace App\Controller;

use App\Entity\AENews;
use App\Entity\ArticleCategory;
use App\Entity\EventCategory;
use App\Entity\Project;
use App\Entity\ProjectCategory;
use App\Entity\RessourcePage;
use App\Entity\RoomBook;
use App\Form\RessourcePageType;
use DateTime;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EspaceAdminController extends AbstractController
{

    /*
     * ============================
     * HOME
     * ============================
     */

    /**
     * @Route("/espace-admin", name="espace_admin")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index()
    {
        return $this->render('espace_admin/espace_admin.html.twig');
    }

    /**
     * @Route("/espace-admin/projets", name="espace_admin_projects")
     * @IsGranted("ROLE_ADMIN")
     */
    public function projects()
    {
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $projects = $rep->findAll();

        return $this->render('espace_admin/espace_admin_projects.html.twig', [
            'projects' => $projects
        ]);
    }

    /**
     * @Route("/espace-admin/categories", name="espace_admin_categories")
     * @IsGranted("ROLE_ADMIN")
     */
    public function categories()
    {
        $projectsRep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $eventsRep = $this->getDoctrine()->getRepository(EventCategory::class);
        $articlesRep = $this->getDoctrine()->getRepository(ArticleCategory::class);

        $projectsCateg = $projectsRep->findAll();
        $eventsCateg = $eventsRep->findAll();
        $articlesCateg = $articlesRep->findAll();

        return $this->render('espace_admin/espace_admin_categories.html.twig', [
            'projects' => $projectsCateg,
            'events' => $eventsCateg,
            'articles' => $articlesCateg
        ]);
    }

    /**
     * @Route("/espace-admin/admins", name="espace_admin_admins")
     * @IsGranted("ROLE_ADMIN")
     */
    public function admins()
    {
        return $this->render('espace_admin/espace_admin_admins.html.twig', [

        ]);
    }

    /**
     * @Route("/espace-admin/reserv-salles", name="espace_admin_roombooks")
     * @IsGranted("ROLE_ADMIN")
     */
    public function roombooks()
    {
        $rbRep = $this->getDoctrine()->getRepository(RoomBook::class);

        $rbs = $rbRep->findAll();

        return $this->render('espace_admin/espace_admin_roombooks.html.twig', [
            'roombooks' => $rbs
        ]);
    }

    /**
     * @Route("/espace-admin/news", name="espace_admin_news_list")
     * @IsGranted("ROLE_ADMIN")
     */
    public function news()
    {
        $rep = $this->getDoctrine()->getRepository(AENews::class);
        $news = $rep->findAll();

        return $this->render('espace_admin/espace_admin_news.html.twig', [
            "news" => $news
        ]);
    }

    /**
     * @Route("/espace-admin/news/nouveau", name="espace_admin_news_write")
     * @IsGranted("ROLE_ADMIN")
     */
    public function newsWrite()
    {
        return $this->render('espace_admin/espace_admin_news_edit.html.twig');
    }

    /**
     * @Route("/espace-admin/news/{id}", name="espace_admin_news_edit")
     * @IsGranted("ROLE_ADMIN")
     */
    public function newsEdit($id)
    {
        $rep = $this->getDoctrine()->getRepository(AENews::class);
        $news = $rep->find($id);

        return $this->render('espace_admin/espace_admin_news_edit.html.twig', [
            'news' => $news
        ]);
    }

    /**
     * @Route("/espace-admin/ressources", name="espace_admin_ressources")
     * @IsGranted("ROLE_ADMIN")
     */
    public function ressources()
    {
        $rep = $this->getDoctrine()->getRepository(RessourcePage::class);
        $pages = $rep->findBy(array(), array('orderPosition' => 'ASC'));

        return $this->render('espace_admin/espace_admin_ressources.html.twig', [
            'pages' => $pages
        ]);
    }
}
