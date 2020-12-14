<?php


namespace App\Controller;


use App\Entity\Project;
use App\Entity\ProjectPage;
use App\Entity\UploadedImage;
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
use Vich\UploaderBundle\Handler\UploadHandler;

class API_ProjectPageController extends AbstractFOSRestController {

    /**
     * Get a ProjectPage from its ID.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the requested ProjectPage",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ProjectPage doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectPage unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ProjectPage")
     * @Rest\Get(
     *     path = "/api/project-page/{id}",
     *     name = "api_project_page_show",
     *     requirements = { "id"="\d+" }
     * )
     * @View(serializerGroups={"basic", "page-full"})
     * @IsGranted("ROLE_USER")
     * @param Integer $id The ProjectPage ID
     * @return ProjectPage|Response
     */
    public function showPage(int $id) {
        $pageRepo = $this->getDoctrine()->getRepository(ProjectPage::class);
        $page = $pageRepo->find($id);

        if ($page == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune page projet n'a été trouvée avec cet ID."));
            return $response;
        }

        return $page;
    }

    /**
     * Create a new ProjectPage. The user must be a Project admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created ProjectPage",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
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
     *     description="The ProjectPage as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
     * )
     * @OA\Tag(name="ProjectPage")
     * @Rest\Put(
     *     path = "/api/project/{id}/pages",
     *     name = "api_project_page_create",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @View(statusCode=201)
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return ProjectPage|\FOS\RestBundle\View\View|Response
     */
    public function newPage($id, Request $request, ValidatorInterface $validator) {
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

        $json = $request->request->all();
        $page = new ProjectPage();
        $page->setProject($project);
        $page->setOrderPosition($json['order_position']);
        $page->setName($json['name']);
        $page->setHtml($json['html']);
        $page->setPublished($json['published']);

        $errors = $validator->validate($page);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($page);
        $em->flush();

        return $page;
    }

    /**
     * Delete a ProjectPage. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested ProjectPage has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ProjectPage doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectPage unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ProjectPage")
     * @Rest\Delete(
     *     path = "/api/project-page/{id}",
     *     name = "api_project_page_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return Response
     */
    public function deletePage($id) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(ProjectPage::class);
        $page = $rep->find($id);

        if ($page == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune page projet trouvée avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $page->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();

        $response->setContent(Utils::jsonMsg("La page projet a été supprimée."));
        return $response;
    }

    /**
     * Update a ProjectPage. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested ProjectPage has been modified",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ProjectPage doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectPage unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="The ProjectPage as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
     * )
     * @OA\Tag(name="ProjectPage")
     * @Rest\Post(
     *     path = "/api/project-page/{id}",
     *     name = "api_project_page_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @View()
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param \HTMLPurifier $purifier
     * @param UploadHandler $handler
     * @return ProjectPage|\FOS\RestBundle\View\View|object|Response
     */
    public function updatePage($id, Request $request, ValidatorInterface $validator, \HTMLPurifier $purifier, UploadHandler $handler) {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $rep = $this->getDoctrine()->getRepository(ProjectPage::class);
        $page = $rep->find($id);

        if ($page == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune page projet trouvée avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $page->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $json = $request->request->all();

        $page->setPublished($json['published']);
        $page->setHtml($purifier->purify($json['html']));
        $page->setName($json['name']);
        $page->setOrderPosition($json['order_position']);

        // Checking if the ProjectPage Images are in the page
        foreach ($page->getUploadedImages() as $p) {
            if (strpos($page->getHtml(), $p->getFileName()) === false) {
                $handler->remove($p, 'file');
                $em->remove($p);
            }
        }

        $errors = $validator->validate($page);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($page);
        $em->flush();

        return $page;
    }

    /**
     * Get a list of a Project's ProjectPages
     * @OA\Response (
     *     response = 200,
     *     description = "The list of ProjectPages",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=ProjectPage::class)))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Project unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="ProjectPage")
     * @Rest\Get(
     *     path = "/api/project/{id}/pages",
     *     name = "api_project_show_pages",
     *     requirements = { "id"="\d+" }
     * )
     * @View(serializerGroups={"list"})
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return ProjectPage[]|Response
     */
    public function showPages($id) {
        $response = new Response();
        $em = $this->getDoctrine();
        $rep = $em->getRepository(Project::class);
        $project = $rep->find($id);
        if ($project == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun projet trouvé avec cet ID."));
            return $response;
        }

        return $project->getPages()->toArray();
    }

    /**
     * Upload an image for a ProjectPage. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The new image has been saved. The body contains the image URL."
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested ProjectPage doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The ProjectPage unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter (
     *     name = "file",
     *     in="query",
     *     description="The image file.",
     *     @OA\Schema(type="file")
     * )
     * @OA\Tag(name="ProjectPage")
     * @Rest\Post(
     *     path = "/api/project-page/{id}/image",
     *     name = "api_project_page_upload_image",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function uploadImage($id, Request $request): Response {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(ProjectPage::class);
        $pPage = $rep->find($id);

        if ($pPage == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune page projet trouvée avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $pPage->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $image = new UploadedImage();
        $image->setProjectPage($pPage);
        $image->setProject($pPage->getProject());
        $image->setFile($request->files->get('image')['file']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($image);
        $em->flush();

        $response->setContent('/images/uploaded-images/' . $image->getFileName());
        return $response;
    }
}