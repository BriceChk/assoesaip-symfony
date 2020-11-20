<?php


namespace App\Controller;


use App\Entity\News;
use App\Entity\Project;
use App\Utils;
use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class API_NewsController extends AbstractFOSRestController {

    /**
     * Get a News from its ID.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the requested News",
     *     @OA\JsonContent(ref=@Model(type=News::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested News doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The News unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="News")
     * @Rest\Get(
     *     path = "/api/news/{id}",
     *     name = "api_news_show",
     *     requirements = { "id"="\d+" }
     * )
     * @View()
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return News|Response
     */
    public function showNews($id) {
        $newsRepo = $this->getDoctrine()->getRepository(News::class);
        $news = $newsRepo->find($id);

        if ($news == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune actu n'a été trouvée avec cet ID."));
            return $response;
        }

        return $news;
    }

    /**
     * Create a new News. The user must be a Project admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created News",
     *     @OA\JsonContent(ref=@Model(type=News::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request body is not valid"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Project unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="The News as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=News::class))
     * )
     * @OA\Tag(name="Article")
     * @Rest\Put(
     *     path = "/api/project/{id}/news",
     *     name = "api_project_news_create",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @View(statusCode=201)
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return News|\FOS\RestBundle\View\View|Response
     */
    public function createNews($id, Request $request, ValidatorInterface $validator) {
        $response = new Response();
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $project = $rep->find($id);
        if ($project == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun projet trouvé avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $project)) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $news = new News();
        $json = $request->request->all();

        $news->setProject($project);
        $news->setDatePublished(new DateTime());
        $news->setContent($json['content']);
        $news->setLink($json['link']);

        $errors = $validator->validate($news);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();

        if ($json['notify']) {
            //TODO Notification
        }

        return $news;
    }

    /**
     * Delete a News. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested News has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested News doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The News unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="News")
     * @Rest\Delete(
     *     path = "/api/news/{id}",
     *     name = "api_news_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return Response
     */
    public function deleteNews($id) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(News::class);
        $news = $rep->find($id);

        if ($news == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune actu trouvée avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $news->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        if ($news->getArticle() != null) {
            $article = $news->getArticle();
            $article->setPrivate(true);
            $em->persist($article);
        }

        if ($news->getEvent() != null) {
            $event = $news->getEvent();
            $event->setPrivate(true);
            $em->persist($event);
        }

        $em->remove($news);
        $em->flush();

        $response->setContent(Utils::jsonMsg("L'actu a été supprimée."));
        return $response;
    }

}