<?php


namespace App\Controller;


use App\Entity\ArticleCategory;
use App\Entity\EventCategory;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;


class API_CategoriesController extends AbstractFOSRestController {

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

    /*
     *
     * DELETE CATEGORIES
     *
     */


    /**
     * Delete a ProjectCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested ProjectCategory has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ProjectCategory doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectCategory unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ProjectCategory")
     * @Rest\Delete(
     *     path = "/api/project-category/{id}",
     *     name = "api_project_category_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @return Response
     */
    public function deleteCategory($id): Response {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $categ = $em->getRepository(ProjectCategory::class)->find($id);
        if ($categ != null) {
            $em->remove($categ);
            $em->flush();
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent(Utils::jsonMsg("La catégorie projet a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent(Utils::jsonMsg("Aucune catégorie projet n'a été trouvé avec cet ID."));
        return $response;
    }

    /**
     * Delete a ArticleCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested ArticleCategory has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ArticleCategory doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ArticleCategory unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ArticleCategory")
     * @Rest\Delete(
     *     path = "/api/article-category/{id}",
     *     name = "api_article_category_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @return Response
     */
    public function deleteArticleCategory($id): Response {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $categ = $em->getRepository(ArticleCategory::class)->find($id);
        if ($categ != null) {
            $em->remove($categ);
            $em->flush();
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent(Utils::jsonMsg("La catégorie article a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent(Utils::jsonMsg("Aucune catégorie article n'a été trouvé avec cet ID."));
        return $response;
    }

    /**
     * Delete a EventCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested EventCategory has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested EventCategory doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The EventCategory unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="EventCategory")
     * @Rest\Delete(
     *     path = "/api/event-category/{id}",
     *     name = "api_event_category_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @return Response
     */
    public function deleteEventCategory($id): Response {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $categ = $em->getRepository(EventCategory::class)->find($id);
        if ($categ != null) {
            $em->remove($categ);
            $em->flush();
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent(Utils::jsonMsg("La catégorie événements a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent(Utils::jsonMsg("Aucune catégorie événements n'a été trouvé avec cet ID."));
        return $response;
    }

    /*
     *
     * CREATE CATEGORIES
     *
     */

    /**
     * Create a new ProjectCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created ProjectCategory",
     *     @OA\JsonContent(ref=@Model(type=ProjectCategory::class))
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a site admin"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\RequestBody(
     *     description="The ProjectCategory as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=ProjectCategory::class))
     * )
     * @OA\Tag(name="ProjectCategory")
     * @Rest\Put(
     *     path = "/api/project-category",
     *     name = "api_project_category_create",
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View(statusCode=201)
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return ProjectCategory|\FOS\RestBundle\View\View
     */
    public function newCategory(Request $request, ValidatorInterface $validator) {
        $categ = new ProjectCategory();
        $json = $request->request->all();

        $categ->setName($json['name']);
        $categ->setUrl($json['url']);
        $categ->setDescription($json['description']);
        $categ->setVisible($json['visible']);
        $categ->setListOrder($json['list_order']);

        $errors = $validator->validate($categ);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($categ);
        $em->flush();

        return $categ;
    }

    /**
     * Create a new ArticleCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created ArticleCategory",
     *     @OA\JsonContent(ref=@Model(type=ArticleCategory::class))
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a site admin"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\RequestBody(
     *     description="The ArticleCategory as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=ArticleCategory::class))
     * )
     * @OA\Tag(name="ArticleCategory")
     * @Rest\Put(
     *     path = "/api/article-category",
     *     name = "api_article_category_create",
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View(statusCode=201)
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return ArticleCategory|\FOS\RestBundle\View\View
     */
    public function newArticleCategory(Request $request, ValidatorInterface $validator) {
        $categ = new ArticleCategory();
        $json = $request->request->all();

        $categ->setName($json['name']);
        $categ->setColor($json['color']);

        $errors = $validator->validate($categ);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($categ);
        $em->flush();

        return $categ;
    }

    /**
     * Create a new EventCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created EventCategory",
     *     @OA\JsonContent(ref=@Model(type=EventCategory::class))
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a site admin"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\RequestBody(
     *     description="The EventCategory as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=EventCategory::class))
     * )
     * @OA\Tag(name="EventCategory")
     * @Rest\Put(
     *     path = "/api/event-category",
     *     name = "api_event_category_create",
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View(statusCode=201)
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return EventCategory|\FOS\RestBundle\View\View
     */
    public function newEventCategory(Request $request, ValidatorInterface $validator) {
        $categ = new EventCategory();
        $json = $request->request->all();

        $categ->setName($json['name']);
        $categ->setColor($json['color']);

        $errors = $validator->validate($categ);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($categ);
        $em->flush();

        return $categ;
    }

    /*
     *
     * UPDATE CATEGORIES
     *
     */

    /**
     * Update a ProjectCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the updated ProjectCategory",
     *     @OA\JsonContent(ref=@Model(type=ProjectCategory::class))
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a site admin"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ProjectCategory is not found"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\RequestBody(
     *     description="The ProjectCategory as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=ProjectCategory::class))
     * )
     * @OA\Tag(name="ProjectCategory")
     * @Rest\Post(
     *     path = "/api/project-category/{id}",
     *     name = "api_project_category_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View(statusCode=200)
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return ProjectCategory|\FOS\RestBundle\View\View|Response
     */
    public function updateCategory($id, Request $request, ValidatorInterface $validator) {
        $repo = $this->getDoctrine()->getRepository(ProjectCategory::class);
        $categ = $repo->find($id);

        if ($categ == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie projet trouvée avec cet ID."));
            return $response;
        }

        $json = $request->request->all();

        $categ->setName($json['name']);
        $categ->setUrl($json['url']);
        $categ->setDescription($json['description']);
        $categ->setVisible($json['visible']);
        $categ->setListOrder($json['list_order']);

        $errors = $validator->validate($categ);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($categ);
        $em->flush();

        return $categ;
    }

    /**
     * Update an ArticleCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the updated ArticleCategory",
     *     @OA\JsonContent(ref=@Model(type=ArticleCategory::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ArticleCategory is not found"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a site admin"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\RequestBody(
     *     description="The ArticleCategory as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=ArticleCategory::class))
     * )
     * @OA\Tag(name="ArticleCategory")
     * @Rest\Post(
     *     path = "/api/article-category/{id}",
     *     name = "api_article_category_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View(statusCode=200)
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return ArticleCategory|\FOS\RestBundle\View\View|Response
     */
    public function updateArticleCategory($id, Request $request, ValidatorInterface $validator) {
        $repo = $this->getDoctrine()->getRepository(ArticleCategory::class);
        $categ = $repo->find($id);

        if ($categ == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie article trouvée avec cet ID."));
            return $response;
        }
        $json = $request->request->all();

        $categ->setName($json['name']);
        $categ->setColor($json['color']);

        $errors = $validator->validate($categ);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($categ);
        $em->flush();

        return $categ;
    }

    /**
     * Update an EventCategory. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the updated EventCategory",
     *     @OA\JsonContent(ref=@Model(type=EventCategory::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested EventCategory is not found"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a site admin"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\RequestBody(
     *     description="The EventCategory as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=EventCategory::class))
     * )
     * @OA\Tag(name="EventCategory")
     * @Rest\Post(
     *     path = "/api/event-category/{id}",
     *     name = "api_event_category_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View(statusCode=200)
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return EventCategory|\FOS\RestBundle\View\View|Response
     */
    public function updateEventCategory($id, Request $request, ValidatorInterface $validator) {
        $repo = $this->getDoctrine()->getRepository(EventCategory::class);
        $categ = $repo->find($id);

        if ($categ == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie événement trouvée avec cet ID."));
            return $response;
        }
        $json = $request->request->all();

        $categ->setName($json['name']);
        $categ->setColor($json['color']);

        $errors = $validator->validate($categ);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($categ);
        $em->flush();

        return $categ;
    }
}