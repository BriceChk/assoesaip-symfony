<?php


namespace App\Controller;


use App\Entity\EventOccurrence;
use App\Entity\News;
use App\Entity\Project;
use App\Entity\ProjectCategory;
use App\Entity\User;
use App\Utils;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class API_ProjectCategoryController extends AbstractFOSRestController {

    /**
     * Get all visible Categories.
     * @OA\Response (
     *     response = 200,
     *     description = "The list of Categories",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=ProjectCategory::class)))
     * )
     * @OA\Tag(name="ProjectCategory")
     * @Rest\Get(
     *     path = "/api/project-category",
     *     name = "api_project_category_list"
     * )
     * @View
     * @IsGranted("ROLE_USER")
     */
    public function listVisibleCategories() {
        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        return $rep->findBy(['visible' => true], ['order' => 'ASC']);
    }

    /**
     * Get all Projects of a Category.
     * @OA\Response (
     *     response = 200,
     *     description = "The list of Projects",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=Project::class)))
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectCategory unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ProjectCategory")
     * @Rest\Get(
     *     path = "/api/project-category/{id}",
     *     name = "api_project_category_project_list"
     * )
     * @View(serializerGroups={"categProjectList"})
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return Project[]|Response
     */
    public function listCategoryProjects($id) {
        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categ = $rep->find($id);

        if ($categ == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie de projet n'a été trouvée avec cet ID."));
            return $response;
        }

        return $categ->getProjects()->filter(function (Project $p) {
            return $p->getType() == 'Association' && ($p->getCampus() == $this->getUser()->getCampus() || $this->isGranted('ROLE_ADMIN'));
        })->getValues();
    }

    /**
     * Get the lastest News of a Category.
     * @OA\Response (
     *     response = 200,
     *     description = "The list of News",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=News::class)))
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectCategory unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ProjectCategory")
     * @Rest\Get(
     *     path = "/api/project-category/{id}/news",
     *     name = "api_project_category_news_list"
     * )
     * @View(serializerGroups={"news"})
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return News[]|Response
     */
    public function listCategoryNews($id) {
        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categ = $rep->find($id);

        if ($categ == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie de projet n'a été trouvée avec cet ID."));
            return $response;
        }

        $newsRepo = $this->getDoctrine()->getRepository(News::class);

        if ($this->isGranted('ROLE_ADMIN')) {
            return $newsRepo->findCategoryNews($categ);
        } else {
            /** @var User $user */
            $user = $this->getUser();
            return $newsRepo->findCategoryNews($categ, $user->getCampus());
        }

    }

    /**
     * Get next EventOccurences of a ProjectCategory.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the array of EventOccurences",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=EventOccurrence::class, groups={"eventOccList"})))
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectCategory unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ProjectCategory")
     * @Rest\Get(
     *     path = "/api/project-category/{id}/next-event-occurrences",
     *     name = "api_event_show_next_categ_occ"
     * )
     * @View(serializerGroups={"eventOccList"})
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return \FOS\RestBundle\View\View|Response
     */
    public function listCategoryEvents($id) {
        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categ = $rep->find($id);

        if ($categ == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie de projet n'a été trouvée avec cet ID."));
            return $response;
        }

        $rep = $this->getDoctrine()->getRepository(EventOccurrence::class);
        if ($this->isGranted('ROLE_ADMIN')) {
            return $rep->findCategoryNext($categ);
        } else {
            /** @var User $user */
            $user = $this->getUser();
            return $rep->findCategoryNext($categ, $user->getCampus());
        }
    }

}