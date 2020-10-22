<?php

namespace App\Controller;

use App\Entity\News;
use App\Entity\ProjectCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/categorie/{url}", name="category")
     */
    public function index($url)
    {
        $repo = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categ = $repo->findOneByUrl($url);

        if ($categ == null) {
            throw $this->createNotFoundException("Cette catégorie n'existe pas");
        }

        $user = $this->getUser();

        //TODO Ajouter la gestion des pages (limiter le nombre de news)
        $news = array();
        foreach ($categ->getProjects() as $p) {
            if ($user != null && $p->getCampus() != $user->getCampus() && !$this->isGranted('ROLE_ADMIN')) {
                continue;
            }
            $news = array_merge($news, $p->getNews()->toArray());
        }

        return $this->render('category.html.twig', [
            'category' => $categ,
            'news' => $news
        ]);
    }
}
