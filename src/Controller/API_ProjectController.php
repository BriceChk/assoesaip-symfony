<?php
namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectCategory;
use App\Entity\ProjectMember;
use App\Entity\User;
use App\Form\ProjectLogoType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class API_ProjectController extends AbstractFOSRestController {
    /**
     * Get a Project from its ID.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the requested Project",
     *     @OA\JsonContent(ref=@Model(type=Project::class))
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
     * @OA\Tag(name="Project")
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
    public function showProject(int $id) {
        //TODO Groupe de serialization pour éviter d'avoir le parentProject entier                                                                                                                                                              
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
     * Create a new Project. The user must be a site admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created Project",
     *     @OA\JsonContent(ref=@Model(type=Project::class))
     * )
     * @OA\Response (
     *     response = 500,
     *     description = "An error occured while saving the object"
     * )
     * @OA\RequestBody(
     *     description="The Project as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=Project::class))
     * )
     * @OA\Tag(name="Project")
     * @Rest\Put(
     *     path = "/api/project",
     *     name = "api_project_create"
     * )
     * @View(statusCode=201)
     * @ParamConverter("project", converter="fos_rest.request_body")
     * @IsGranted("ROLE_ADMIN")
     * @param Project $project
     * @return Project|Response
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
     * Update a Project. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the updated Project",
     *     @OA\JsonContent(ref=@Model(type=Project::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
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
     *     description="The Project as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=Project::class))
     * )
     * @OA\Tag(name="Project")
     * @Rest\Post(
     *     path = "/api/project/{id}",
     *     name = "api_project_update",
     *     requirements = { "id"="\d+" }
     * )
     * @View(statusCode=200)
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Project|\FOS\RestBundle\View\View|object|Response
     */
    public function updateProject($id, Request $request, ValidatorInterface $validator) {
        $response = new Response();
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
        $project->setSocial($uProject['social']);

        $cat = $this->getDoctrine()->getRepository(ProjectCategory::class)->find($uProject['category']);
        $project->setCategory($cat);

        if ($project->getType() == 'Club' || $project->getType() == 'Projet') {
            $parent = $this->getDoctrine()->getRepository(Project::class)->find($uProject['parentProject']);
            $project->setParentProject($parent);
        }  else {
            $project->setParentProject(null);
        }

        $errors = $validator->validate($project);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($project);
        $em->flush();

        return $project;
    }

    /**
     * Delete a Project. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested Project has been deleted"
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
     * @OA\Tag(name="Project")
     * @Rest\Delete(
     *     path = "/api/project/{id}",
     *     name = "api_project_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @param Project $project
     * @return Response
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

    /**
     * Get a Project's list of ProjectMember from its ID.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the ProjectMember array",
    *      @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=ProjectMember::class)))
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
     * @OA\Tag(name="Project")
     * @Rest\Get(
     *     path = "/api/project/{id}/members",
     *     name = "api_project_show_members",
     *     requirements = { "id"="\d+" }
     * )
     * @View
     * @IsGranted("ROLE_USER")
     * @param Integer $id The Project ID
     * @return array|Response
     */
    public function showMembers(int $id) {
        $pRep = $this->getDoctrine()->getRepository(Project::class);
        $project = $pRep->find($id);
        if ($project == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($this->jsonMsg("Aucun projet trouvé avec cet ID."));
            return $response;
        }

        return $project->getMembers();
    }

    /**
     * Update a Project's ProjectMembers. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the updated ProjectMember list",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=ProjectMember::class)))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
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
     *     description="The ProjectMember list as a JSON array (only the user's ID, not the whole object as on the schema !)",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=ProjectMember::class)))
     * )
     * @OA\Tag(name="Project")
     * @Rest\Post(
     *     path = "/api/project/{id}/members",
     *     name = "api_project_update_members",
     *     requirements = { "id"="\d+" }
     * )
     * @View(statusCode=200)
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return array|\FOS\RestBundle\View\View|Response
     */
    public function saveMembers($id, Request $request, ValidatorInterface $validator) {
        $response = new Response();
        $em = $this->getDoctrine();
        $rep = $em->getRepository(Project::class);
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

        // Check the validity of the members
        $usersRep = $em->getRepository(User::class);
        $membersList = $request->request->all();

        $mList = array();

        foreach ($membersList as $member) {
            $m = new ProjectMember();
            $m->setIntroduction($member['introduction']);
            $m->setAdmin($member['admin']);
            $m->setOrderPosition($member['orderPosition']);
            $m->setRole($member['role']);
            $m->setProject($project);
            $user = $usersRep->find($member['user']);
            if ($user == null) {
                continue;
            }
            $m->setUser($user);

            $errors = $validator->validate($m);
            if (count($errors)) {
                return $this->view($errors, Response::HTTP_BAD_REQUEST);
            }

            $mList[] = $m;
        }

        // Peut être au lieu de tout supprimer, faire le tour des membres et vérifier si ils existent pour les modifier ou supprimer ?

        // Delete all members
        $projectMembersRep = $em->getRepository(ProjectMember::class);
        $r = $projectMembersRep->findBy(['project' => $project]);
        foreach ($r as $p) {
            $em->getManager()->remove($p);
        }
        $em->getManager()->flush();

        // Add the correct list
        foreach ($mList as $m) {
            $em->getManager()->persist($m);
        }

        $em->getManager()->flush();
        return $mList;
    }

    /**
     * Update a Project's logo. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The new logo has been saved"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The request is not valid"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Project unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter (
     *     name = "logoFile",
     *     in="query",
     *     description="The logo file. Must be an image.",
     *     @OA\Schema(type="file")
     * )
     * @OA\Tag(name="Project")
     * @Rest\Post(
     *     path = "/api/project/{id}/logo",
     *     name = "api_project_update_logo",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @return \FOS\RestBundle\View\View|Response
     */
    public function saveLogo($id, Request $request, ValidatorInterface $validator) {
        $response = new Response();
        $em = $this->getDoctrine();
        $rep = $em->getRepository(Project::class);
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


        $project->setLogoFile($request->files->get('project')['logoFile']);

        $errors = $validator->validate($project);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($project);
        $em->flush();

        $response->setContent($this->jsonMsg("Le logo a été enregistré !"));
        return $response;
    }

    private function jsonMsg($text) {
        return '[{ "message": "'.$text.'" }]';
    }

    private function getCategory($id) {
        $rep = $this->getDoctrine()->getRepository(ProjectCategory::class);
        return $rep->find($id);
    }
}