<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PreventionController extends AbstractController
{
    /**
     * @Route("/prevention", name="prevention_home")
     */
    public function home(): Response
    {
        return $this->render('prevention/home.html.twig', []);
    }

    /**
     * @Route("/prevention/forum", name="prevention_forum")
     */
    public function forum(): Response
    {
        return $this->render('prevention/forum.html.twig', []);
    }

    /**
     * @Route("/prevention/topic/{id}", name="prevention_view_topic")
     */
    public function view_topic(): Response
    {
        return $this->render('prevention/view_topic.html.twig', []);
    }

    /**
     * @Route("/prevention/topic/add", name="prevention_add_topic")
     */
    public function add_topic(): Response
    {
        return $this->render('prevention/add_topic.html.twig', []);
    }

    /**
     * @Route("/prevention/chat", name="prevention_chat")
     */
    public function chat(): Response
    {
        return $this->render('prevention/chat.html.twig', []);
    }

    /**
     * @Route("/prevention/profile", name="prevention_profile")
     */
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->render('prevention/profile.html.twig', []);
    }

    /**
     * @Route("/prevention/admin/update_home", name="update_home")
     */
    public function update_home(): Response
    {
        return $this->render('prevention/update_home.html.twig', []);
    }

    /**
     * @Route("/prevention/admin/manage_forum", name="manage_forum")
     */
    public function manage_forum(): Response
    {
        return $this->render('prevention/manage_forum.html.twig', []);
    }
}
