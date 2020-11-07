<?php
namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectCategory;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class API_ProjectController extends AbstractFOSRestController {
    /**
     * Get a Project from its id.
     * @Rest\Get(
     *     path = "/api/project/{id}",
     *     name = "api_project_show",
     *     requirements = { "id"="\d+" }
     * )
     * @View
     * @IsGranted("ROLE_USER")
     * @param Integer $id The Project ID
     * @return Project|Response
     */
    public function showProject($id) {
        $em = $this->getDoctrine()->getRepository(Project::class);
        $rb = $em->find($id);
        if ($rb == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($this->jsonMsg("Aucun projet n'a été trouvé avec cet ID."));
            return $response;
        }
        return $rb;
    }

    /**
     * @Rest\Put(
     *     path = "/api/project",
     *     name = "api_project_create"
     * )
     * @View(statusCode=201)
     * @ParamConverter("project", converter="fos_rest.request_body")
     * @IsGranted("ROLE_ADMIN")
     */
    public function newProject(Project $project)
    {
        //TODO Check si l'URL est bien unique
        $em = $this->getDoctrine()->getManager();
        $em->persist($project);
        $em->flush();

        if ($em->contains($project)) {
            return $project;
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent($this->jsonMsg("Une erreur s'est produite lors de l'enregistrement."));
        return $response;
    }

    //TODO API pour modifier l'URL (admin)

    /**
     *
     * @Rest\Post(
     *     path = "/api/project/{id}",
     *     name = "api_project_update",
     *     requirements = { "id"="\d+" }
     * )
     * @View(statusCode=201)
     * @IsGranted("ROLE_USER")
     */
    public function updateProject($id, Request $request) {
        $response = new Response();
//TODO some kind of data validation?
        $rep = $this->getDoctrine()->getRepository(Project::class);
        $project = $rep->find($id);
        if ($project == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($this->jsonMsg("Aucun projet trouvé avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $project)) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent($this->jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        // The json received as PHP array
        $uProject = $request->request->all();

        $project->setName($uProject['name']);
        $project->setType($uProject['type']);
        $project->setDescription($uProject['description']);
        $project->setKeywords($uProject['keywords']);
        $project->setEmail($uProject['email']);
        $project->setSocial(json_encode($uProject['social']));


        $errors = $this->get('validator')->validate($project);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        //TODO update all properties, validate

        $em = $this->getDoctrine()->getManager();
        $em->persist($project);
        $em->flush();

        return $project;
    }

    /**
     * @Rest\Delete(
     *     path = "/api/project/{id}",
     *     name = "api_project_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteProject(Project $project) {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        if ($em->contains($project)) {
            $em->remove($project);
            $em->flush();
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent($this->jsonMsg("Le projet a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent($this->jsonMsg("Aucun projet n'a été trouvé avec cet ID."));
        return $response;
    }

    private function jsonMsg($text) {
        return '{ "message": "'.$text.'" }';
    }

    private function getCategory($id) {
        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        return $rep->find($id);
    }
}