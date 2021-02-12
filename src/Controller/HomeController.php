<?php

namespace App\Controller;

use App\Entity\AssoEsaipSettings;
use App\Entity\News;
use App\Entity\Project;
use App\Entity\ProjectCategory;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @IsGranted("ROLE_USER")
     */
    public function index()
    {
        $categRepo = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categories = $categRepo->findBy(array('visible' => 1), array('listOrder' => 'ASC'));

        // TODO Mettre un bouton charger + // Vérifier que la limite de 20 articles + events marche bien

        /** @var User $user */
        $user = $this->getUser();

        $newsRepo = $this->getDoctrine()->getRepository(News::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            $news = $newsRepo->findLatestNews(20);
        } else {
            /** @var User $user */
            $user = $this->getUser();
            $news = $newsRepo->findLatestNews(20, $user->getCampus());
        }

        return $this->render('home.html.twig', [
            'categories' => $categories,
            'news' => $news
        ]);
    }
}
