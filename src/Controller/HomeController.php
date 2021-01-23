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
        $categories = $categRepo->findBy(array('visible' => 1), array('order' => 'ASC'));

        // TODO Mettre un bouton charger + // Vérifier que la limite de 20 articles + events marche bien

        /** @var User $user */
        $user = $this->getUser();
        $campus = $user != null ? $user->getCampus() : 'A';

        $projectRepo = $this->getDoctrine()->getRepository(Project::class);
        if ($campus == 'A') {
            $projects = $projectRepo->findAll();
        } else {
            $projects = $projectRepo->findBy(['campus' => $campus], null, 30, null);
        }

        $news = array();
        foreach ($projects as $p) {
            $news = array_merge($news, $p->getNews()->toArray());
        }

        return $this->render('home.html.twig', [
            'categories' => $categories,
            'news' => $news
        ]);
    }
}
