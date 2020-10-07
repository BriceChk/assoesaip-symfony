<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ProjectMember;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/article/{url}", name="article")
     * @IsGranted("ROLE_USER")
     */
    public function index($url)
    {
        $em = $this->getDoctrine()->getRepository(Article::class);
        $article = $em->findOneBy(['url' => $url]);

        if ($article == null) {
            throw $this->createNotFoundException("Cet article n'existe pas");
        }

        $em = $this->getDoctrine()->getRepository(ProjectMember::class);
        $member = $em->findOne($article->getProject(), $article->getAuthor());

        return $this->render('article.html.twig', [
            'article' => $article,
            'author' => $member
        ]);
    }
}
