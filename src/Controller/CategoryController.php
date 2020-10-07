<?php

namespace App\Controller;

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

        return $this->render('category.html.twig', [
            'category' => $categ,
        ]);
    }
}
