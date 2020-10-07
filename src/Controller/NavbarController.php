<?php

namespace App\Controller;

use App\Entity\ProjectCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NavbarController extends AbstractController
{

    public function renderCategories(bool $onlyVisible)
    {
        $em = $this->getDoctrine()->getRepository(ProjectCategory::class);
        if ($onlyVisible) {
            $categories = $em->findBy(['visible' => $onlyVisible], ['order' => 'ASC']);
        } else {
            $categories = $em->findAll([], ['order' => 'ASC']);
        }


        return $this->render('macros/_navbar_categories.html.twig', [
            'categories' => $categories,
        ]);
    }
}
