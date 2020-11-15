<?php

namespace App\Controller;

use App\Entity\RessourcePage;
use App\Form\RessourcePageType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class API_RessourcePageController extends AbstractController
{
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
            $response->setContent($this->jsonMsg("Aucne page ressource n'a été trouvée avec cet ID."));
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
            $response->setContent($this->jsonMsg("Aucne page ressource n'a été trouvée avec cet URL."));
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
                $response->setContent($this->jsonMsg('Une page ressource existe déjà avec cette URL.'));
                return $response;
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            return $page;
        }

        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent($this->jsonMsg("Une erreur s'est produite lors de l'enregistrement."));
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
     */
    public function updateRessource(RessourcePage $page, Request $request) {
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
                $response->setContent($this->jsonMsg("Aucune page ressource n'a été trouvée avec cet ID."));
                return $response;
            }

            // For some reason this doesn't get updated with by form
            $updatedPage->setPublished($data['published']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            return $page;
        }

        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent($this->jsonMsg("Une erreur s'est produite lors de l'enregistrement. " . $form->getErrors(true, false)));
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
            $response->setContent($this->jsonMsg("La page a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent($this->jsonMsg("Aucne page n'a été trouvée avec cet ID."));
        return $response;
    }

    private function jsonMsg($text) {
        return '{ "message": "'.$text.'" }';
    }
}