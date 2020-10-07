<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/{url}", name="project")
     * @Route("/projets/{url}", name="project_old")
     * @IsGranted("ROLE_USER")
     */
    public function index($url)
    {
        $repo = $this->getDoctrine()->getRepository(Project::class);
        $project = $repo->findOneByUrl($url);

        if ($project == null) {
            // We try to redirect to a category URL if someone used an old URL
            $categRepo = $this->getDoctrine()->getRepository(ProjectCategory::class);
            $categ = $categRepo->findOneByUrl($url);
            if ($categ) {
                return $this->redirectToRoute('category', ['url' => $url], 301);
            }

            throw $this->createNotFoundException("Ce projet n'existe pas");
        }

        return $this->render('project.html.twig', [
            'project' => $project
        ]);
    }
}
