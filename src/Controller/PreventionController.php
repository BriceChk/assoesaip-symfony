<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Message;
use App\Form\TopicType;
use App\Entity\AssoEsaipSettings;
use App\Entity\TopicResponse;
use App\Repository\MessageRepository;
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
        $presentation = 'la presentation';
        $settingsRep = $this->getDoctrine()->getRepository(AssoEsaipSettings::class);
        //$pres = $settingsRep->getPresentationPolePrevention();
        return $this->render('prevention/home.html.twig', []);
    }

    /**
     * @Route("/prevention/forum", name="prevention_forum")
     */
    public function forum(): Response
    {
        $doc = $this->getDoctrine();
        $topics = $doc->getRepository(Topic::class)->findBy(['status' => 'Validé'], ['creationDate' => 'DESC']);
        $tags = $doc->getRepository(Tag::class)->findAll();
        return $this->render('prevention/forum.html.twig', [
            'topics' => $topics,
            'tags' => $tags
        ]);
    }

    /**
     * @Route("/prevention/topic/add", name="prevention_add_topic")
     */
    public function add_topic(Request $request): Response
    {
        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setCreationDate(new \DateTime());
            $topic->setAuthor($this->getUser());
            $topic->setIsAnonymous($form->get('is_anonymous')->getData());
            $topic->setStatus('En attente');

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
     * @Route("/prevention/topic/{id}", name="prevention_view_topic")
     */
    public function view_topic(Topic $topic): Response
    {
        return $this->render('prevention/view_topic.html.twig', [
            'topic' => $topic
        ]);
    }

    /**
     * @Route("/prevention/profile", name="prevention_profile")
     */
    public function profile(Request $request): Response
    {
        $user = $this->getUser();

        if ($request->get('is_anonymous')) {
            $user->setIsAnonymous($request->get('is_anonymous') == 'on' ? false : true);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('prevention_profile');
        }

        return $this->render('prevention/profile.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/prevention/chat", name="prevention_chat")
     */
    public function chat(): Response
    {
        $doc = $this->getDoctrine();
        $messages = $doc->getRepository(Message::class)->findBy(['author' => $this->getUser()], ['messageDate' => 'ASC']);
        return $this->render('prevention/chat.html.twig', [
            'messages' => $messages
        ]);
    }

    /**
     * @Route("/prevention/admin/chat", name="prevention_admin_chat")
     */
    public function admin_chat(MessageRepository $messageRepository): Response
    {
        $authors = $messageRepository->getListOfAuthors();
        $rep = $this->getDoctrine()->getRepository(Message::class);
        $messages = $authors ? $rep->findBy(['author' => $authors[0]], ['messageDate' => 'ASC']) : null;
        return $this->render('prevention/chat_admin.html.twig', [
            'messages' => $messages,
            'authors' => $authors
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
    public function manage_forum(Request $request): Response
    {
        $doc = $this->getDoctrine();
        $topics = $doc->getRepository(Topic::class)->findAll();
        $responses = $doc->getRepository(TopicResponse::class)->findAll();

        if ($request->get('objectId')) {
            $rep = str_contains($request->get('objectId'), 'topic') ? $doc->getRepository(Topic::class) : $doc->getRepository(TopicResponse::class);
            $object = $rep->findOneBy(['id' => (explode('|', $request->get('objectId'))[1])]);
            $object->setRejectionMessage($request->get('rejectionMessage'));
            $object->setStatus($request->get('status'));
            $entityManager = $doc->getManager();
            $entityManager->persist($object);
            $entityManager->flush();
            return $this->redirectToRoute('manage_forum');
        }

        return $this->render('prevention/manage_forum.html.twig', [
            'topics' => $topics,
            'responses' => $responses
        ]);
    }
}
