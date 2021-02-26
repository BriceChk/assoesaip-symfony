<?php

namespace App\Controller;

use App\Entity\RessourcePage;
use App\Entity\UploadedImage;
use App\Form\RessourcePageType;
use App\Utils;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Handler\UploadHandler;

class API_RessourcePageController extends AbstractController
{
    //TODO Validation / API doc / get rid of the forms

    /**
     * Get a RessourcePage from its ID.
     * @Rest\Get(
     *     path = "/api/ressource-pages/{id}",
     *     name = "api_ressource_page_show",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RessourcePage")
     * @View
     * @IsGranted("ROLE_USER")
     */
    public function showRessource($id) {
        $em = $this->getDoctrine()->getRepository(RessourcePage::class);
        $page = $em->find($id);
        if ($page == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucne page ressource n'a été trouvée avec cet ID."));
            return $response;
        }
        return $page;
    }

    /**
     * Get a RessourcePage from its URL property.
     * @Rest\Get(
     *     path = "/api/ressource-pages/{url}",
     *     name = "api_ressource_page_show_url"
     * )
     * @OA\Tag(name="RessourcePage")
     * @View
     * @IsGranted("ROLE_USER")
     */
    public function showRessourceByUrl($url) {
        $em = $this->getDoctrine()->getRepository(RessourcePage::class);
        $page = $em->findOneBy(['url' => $url]);
        if ($page == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucne page ressource n'a été trouvée avec cet URL."));
            return $response;
        }
        return $page;
    }

    /**
     * Create a new RessourcePage.
     * @Rest\Put(
     *     path = "/api/ressource-pages",
     *     name = "api_ressource_page_create"
     * )
     * @OA\Tag(name="RessourcePage")
     * @View(statusCode=201)
     * @IsGranted("ROLE_ADMIN")
     */
    public function newRessource(Request $request)
    {
        $page = new RessourcePage();
        $form = $this->createForm(RessourcePageType::class, $page);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        $response = new Response();

        if ($form->isSubmitted() && $form->isValid()) {
            $rep = $this->getDoctrine()->getRepository(RessourcePage::class);
            $existingPage = $rep->findOneBy(['url' => $page->getUrl()]);
            if ($existingPage != null) {
                $response->setStatusCode(Response::HTTP_CONFLICT);
                $response->setContent(Utils::jsonMsg('Une page ressource existe déjà avec cette URL.'));
                return $response;
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            return $page;
        }

        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent(Utils::jsonMsg("Une erreur s'est produite lors de l'enregistrement."));
        return $response;
    }

    /**
     * Update a RessourcePage.
     * @Rest\Post(
     *     path = "/api/ressource-pages/{id}",
     *     name = "api_ressource_page_update",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RessourcePage")
     * @View(statusCode=201)
     * @IsGranted("ROLE_ADMIN")
     * @param RessourcePage $page
     * @param Request $request
     * @param UploadHandler $handler
     * @return RessourcePage|Response
     */
    public function updateRessource(RessourcePage $page, Request $request, UploadHandler $handler) {
        $updatedPage = $page;
        $form = $this->createForm(RessourcePageType::class, $updatedPage);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        $response = new Response();

        if ($form->isSubmitted() && $form->isValid()) {
            $rep = $this->getDoctrine()->getRepository(RessourcePage::class);
            $existingPage = $rep->find($page->getId());
            if ($existingPage == null) {
                $response->setStatusCode(Response::HTTP_CONFLICT);
                $response->setContent(Utils::jsonMsg("Aucune page ressource n'a été trouvée avec cet ID."));
                return $response;
            }

            // For some reason this doesn't get updated with by form
            $updatedPage->setPublished($data['published']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);

            // Checking if the Images are in the page
            foreach ($page->getUploadedImages() as $p) {
                if (strpos($page->getHtml(), $p->getFileName()) === false) {
                    $handler->remove($p, 'file');
                    $em->remove($p);
                }
            }

            $em->flush();
            return $page;
        }

        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent(Utils::jsonMsg("Une erreur s'est produite lors de l'enregistrement. " . $form->getErrors(true, false)));
        return $response;
    }

    /**
     * Delete a RessourcePage.
     * @Rest\Delete(
     *     path = "/api/ressource-pages/{id}",
     *     name = "api_ressource_page_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RessourcePage")
     * @View(statusCode=200)
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteRessource(RessourcePage $page) {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        if ($em->contains($page)) {
            $em->remove($page);
            $em->flush();
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent(Utils::jsonMsg("La page a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent(Utils::jsonMsg("Aucne page n'a été trouvée avec cet ID."));
        return $response;
    }

    /**
     * Upload an image for a RessourcePage. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The new image has been saved. The body contains the image URL."
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested RessourcePage doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The RessourcePage unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter (
     *     name = "file",
     *     in="query",
     *     description="The image file.",
     *     @OA\Schema(type="file")
     * )
     * @OA\Tag(name="RessourcePage")
     * @Rest\Post(
     *     path = "/api/ressource-page/{id}/image",
     *     name = "api_ressource_page_upload_image",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function uploadImage($id, Request $request): Response {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(RessourcePage::class);
        $rPage = $rep->find($id);

        if ($rPage == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune page ressource trouvée avec cet ID."));
            return $response;
        }

        $image = new UploadedImage();
        $image->setRessourcePage($rPage);
        $image->setFile($request->files->get('image')['file']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($image);
        $em->flush();

        $response->setContent('https://' . $request->getHttpHost() . '/images/uploaded-images/' . $image->getFileName());
        return $response;
    }
}