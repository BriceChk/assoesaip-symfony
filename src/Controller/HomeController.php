<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\AssoEsaipSettings;
use App\Entity\Event;
use App\Entity\ProjectCategory;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $categRepo = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categories = $categRepo->findBy(array('visible' => 1), array('order' => 'ASC'));

        // TODO Mettre un bouton charger + // Vérifier que la limite de 20 articles + events marche bien
        // TODO Faire fonctionner la recherche

        /** @var User $user */
        $user = $this->getUser();
        $campus = $user != null ? $user->getCampus() : 'A';

        $articleRepo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $articleRepo->findByPage(1, 10, $campus);

        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepo->findByPage(1);

        $merged = array_merge($articles, $events);

        // Home message
        $settingsRepo = $this->getDoctrine()->getRepository(AssoEsaipSettings::class);

        return $this->render('home.html.twig', [
            'categories' => $categories,
            'articles' => $merged,
            'homeMessage' => $settingsRepo->getHomeMessage()
        ]);
    }
}
