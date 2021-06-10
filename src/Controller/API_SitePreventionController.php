<?php


namespace App\Controller;

use App\Entity\User;
use App\Utils;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class API_SitePreventionController extends AbstractController
{


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
     *     path = "/api/prevention/{id}",
     *     name = "api_prevention_add",
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
    public function addPrevention($id): Response {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $admin = $em->getRepository(User::class)->find($id);

        if ($admin == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Cet utilisateur n'existe pas"));
            return $response;
        }

        $roles = $admin->getRoles();
        if (($key = array_search('ROLE_PREV', $roles)) !== false) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Cet utilisateur est déjà un membre du pôle prévention"));
            return $response;
        } else {
            $roles[] = 'ROLE_PREV';
        }
        $admin->setRoles($roles);

        $em->persist($admin);
        $em->flush();

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent(Utils::jsonMsg("L'utilisateur a été ajouté aux membres du pôle prévention"));
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
     *     path = "/api/prevention/{id}",
     *     name = "api_preventionn_remove",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="Administration")
     * @IsGranted("ROLE_ADMIN")
     * @param $id
     * @return Response
     */
    public function removePrevention($id): Response
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $prevention = $em->getRepository(User::class)->find($id);

        if ($prevention == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Cet utilisateur n'existe pas"));
            return $response;
        }

        if ($prevention == $this->getUser()) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous ne pouvez pas vous enlever vous même des membres du pôle prévention"));
            return $response;
        }

        $roles = $prevention->getRoles();
        if (($key = array_search('ROLE_PREV', $roles)) !== false) {
            unset($roles[$key]);
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Cet utilisateur n'est pas membre du pôle prévention"));
            return $response;
        }
        $prevention->setRoles($roles);

        $em->persist($prevention);
        $em->flush();

        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent(Utils::jsonMsg("L'utilisateur a été enlevé des membres du pôle prévention"));
        return $response;
    }
}
