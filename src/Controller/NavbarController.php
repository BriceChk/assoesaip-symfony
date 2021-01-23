<?php

namespace App\Controller;

use App\Entity\ProjectCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NavbarController extends AbstractController
{

    public function renderCategories(bool $isAdmin, bool $withProjects = true)
    {
        $em = $this->getDoctrine()->getRepository(ProjectCategory::class);
        if ($isAdmin) {
            $categories = $em->findBy([], ['listOrder' => 'ASC']);
        } else {
            $categories = $em->findBy(['visible' => true], ['listOrder' => 'ASC']);
        }

        $view = $withProjects ? 'macros/_navbar_projects.html.twig' : 'macros/_navbar_categories.html.twig';

        return $this->render($view, [
            'categories' => $categories,
        ]);
    }
}
