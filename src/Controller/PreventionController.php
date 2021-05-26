<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Topic;
use App\Form\TopicType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $doc = $this->getDoctrine();
        $topics = $doc->getRepository(Topic::class)->findAll();
        $tags = $doc->getRepository(Tag::class)->findAll();
        return $this->render('prevention/forum.html.twig', [
            'topics' => $topics,
            'tags' => $tags
        ]);
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
    public function add_topic(Topic $topic, Request $request): Response
    {
        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setCreationDate(new \DateTime());
            $topic->setAuthor($this->getUser());
            $topic->setIsAnonymous($form->get('is_anonymous')->getData());
            $topic->setStatus('pending');

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute('prevention_forum');
        }

        return $this->render('prevention/add_topic.html.twig', [
            'topic' => $topic,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/prevention/chat", name="prevention_chat")
     */
    public function chat(): Response
    {
        $doc = $this->getDoctrine();
        $messages = $doc->getRepository(Messages::class)->findBy(['user' => $this->getUser()], ['messageDate' => 'ASC']);
        return $this->render('prevention/chat.html.twig', [
            'messages' => $messages
        ]);
    }

    /**
     * @Route("/prevention/profile", name="prevention_profile")
     */
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->render('prevention/profile.html.twig', [
            'user' => $user
        ]);
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
