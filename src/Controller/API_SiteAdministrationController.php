<?php


namespace App\Controller;

use App\Entity\User;
use App\Utils;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class API_SiteAdministrationController extends AbstractController {

    /**
     * @OA\Response (
     *     response = 200,
     *     description = "The User has been granted administrator privileges"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The User is already an administrator"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested User doesn't exist"
     * )
     * @Rest\Put(
     *     path = "/api/admin/{id}",
     *     name = "api_admin_add",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The User unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Administration")
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @return Response
     */
    public function addAdmin($id): Response {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $admin = $em->getRepository(User::class)->find($id);

        if ($admin == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Cet utilisateur n'existe pas"));
            return $response;
        }

        $roles = $admin->getRoles();
        if (($key = array_search('ROLE_ADMIN', $roles)) !== false) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Cet utilisateur est déjà administrateur"));
            return $response;
        } else {
            $roles[] = 'ROLE_ADMIN';
        }
        $admin->setRoles($roles);

        $em->persist($admin);
        $em->flush();

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent(Utils::jsonMsg("L'utilisateur a été ajouté aux administrateurs"));
        return $response;
    }

    /**
     * @OA\Response (
     *     response = 200,
     *     description = "The User has been removed from the administrators"
     * )
     * @OA\Response (
     *     response = 400,
     *     description = "The requested User is not an administrator"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "You can't remove yourself from administrators"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested User doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The User unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @Rest\Delete(
     *     path = "/api/admin/{id}",
     *     name = "api_admin_remove",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="Administration")
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @return Response
     */
    public function removeAdmin($id): Response {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $admin = $em->getRepository(User::class)->find($id);

        if ($admin == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Cet utilisateur n'existe pas"));
            return $response;
        }

        if ($admin == $this->getUser()) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous ne pouvez pas vous enlever vous même des administrateurs"));
            return $response;
        }

        $roles = $admin->getRoles();
        if (($key = array_search('ROLE_ADMIN', $roles)) !== false) {
            unset($roles[$key]);
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Cet utilisateur n'est pas administrateur"));
            return $response;
        }
        $admin->setRoles($roles);

        $em->persist($admin);
        $em->flush();

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent(Utils::jsonMsg("L'utilisateur a été enlevé des administrateurs"));
        return $response;
    }
}