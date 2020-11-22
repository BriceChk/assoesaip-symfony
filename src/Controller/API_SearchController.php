<?php


namespace App\Controller;


use App\Entity\Article;
use App\Entity\Event;
use App\Entity\Project;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use OpenApi\Annotations as OA;

class API_SearchController extends AbstractFOSRestController {
//TODO La doc API

    /**
     * Search a user by its name or email. Returns a User JSON array
     * @Rest\Get(
     *     path = "/api/search/user/{term}",
     *     name = "api_search_user"
     * )
     * @View
     * @OA\Tag(name="Search")
     * @IsGranted("ROLE_USER")
     * @param string $term
     * @return User[]
     */
    public function searchUser(string $term) {
        $rep = $this->getDoctrine()->getRepository(User::class);
        return $rep->search($term);
    }

    /**
     * Search a project, article, event. Returns an easy-autocomplete-friendly JSON array
     * @Rest\Get(
     *     path = "/api/search/{term}",
     *     name = "api_search_global"
     * )
     * @OA\Tag(name="Search")
     * @param string $term
     * @return \FOS\RestBundle\View\View
     */
    public function search(string $term) {
        $a = array();

        $rep = $this->getDoctrine()->getRepository(Project::class);
        $projects = $rep->search($term);
        foreach ($projects as $p) {
            $a[] = [
                "name" => $p->getName(),
                "type" => $p->getType(),
                "url" => $this->generateUrl('project', ['url' => $p->getUrl()])
            ];
        }

        $rep = $this->getDoctrine()->getRepository(Event::class);
        $events = $rep->search($term);
        foreach ($events as $e) {
            $a[] = [
                "name" => $e->getTitle(),
                "type" => "Événement",
                "url" => $this->generateUrl('event', ['url' => $e->getUrl()])
            ];
        }

        $rep = $this->getDoctrine()->getRepository(Article::class);
        $articles = $rep->search($term);
        foreach ($articles as $e) {
            $a[] = [
                "name" => $e->getTitle(),
                "type" => "Article",
                "url" => $this->generateUrl('article', ['url' => $e->getUrl()])
            ];
        }

        return $this->view($a);
    }

}