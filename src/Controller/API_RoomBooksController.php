<?php

namespace App\Controller;

use App\Entity\RoomBook;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use phpDocumentor\Reflection\Types\Integer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

class API_RoomBooksController extends AbstractController
{
    /**
     * Get a RoomBook from its id.
     * @Rest\Get(
     *     path = "/api/roombook/{id}",
     *     name = "api_roombook_show",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RoomBook")
     * @View
     * @IsGranted("ROLE_USER")
     * @param Integer $id The RoomBooks ID
     * @return RoomBook|Response
     */
    public function showRoombook($id) {
        //TODO Blacklist some properties on all entities to avoid circular reference using serializer @Groups

        $em = $this->getDoctrine()->getRepository(RoomBook::class);
        $rb = $em->find($id);
        if ($rb == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($this->jsonMsg("Aucune réservation de salle n'a été trouvée avec cet ID."));
            return $response;
        }
        return $rb;
    }

    /**
     * @Rest\Put(
     *     path = "/api/roombook",
     *     name = "api_roombook_create"
     * )
     * @OA\Tag(name="RoomBook")
     * @View(statusCode=201)
     * @ParamConverter("rb", converter="fos_rest.request_body")
     * @IsGranted("ROLE_PROJECT_EDITOR")
     */
    public function newRoombook(RoomBook $rb)
    {
        //TODO Check if the user is an admin of the project
        $em = $this->getDoctrine()->getManager();
        $em->persist($rb);
        $em->flush();

        if ($em->contains($rb)) {
            return $rb;
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent($this->jsonMsg("Une erreur s'est produite lors de l'enregistrement."));
        return $response;
    }

    /**
     * @Rest\Post(
     *     path = "/api/roombook/{id}",
     *     name = "api_roombook_update",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RoomBook")
     * @View(statusCode=201)
     * @ParamConverter("updatedRb", converter="fos_rest.request_body")
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateRoombook(RoomBook $updatedRb, $id) {
        $rep = $this->getDoctrine()->getRepository(RoomBook::class);
        $rb = $rep->find($id);
        if ($rb == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($this->jsonMsg("Aucune réservation de salle trouvée avec cet ID."));
            return $response;
        }

        $rb->setStatus($updatedRb->getStatus());
        $rb->setRoom($updatedRb->getRoom());

        $em = $this->getDoctrine()->getManager();
        $em->persist($rb);
        $em->flush();

        return $rb;
    }

    /**
     * @Rest\Delete(
     *     path = "/api/roombook/{id}",
     *     name = "api_roombooks_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RoomBook")
     * @View(statusCode=200)
     * @IsGranted("ROLE_PROJECT_EDITOR")
     */
    public function deleteRoombook(RoomBook $rb) {
        //TODO Check if the user is an admin of the project
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        if ($em->contains($rb)) {
            $em->remove($rb);
            $em->flush();
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent($this->jsonMsg("La réservation de salle a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent($this->jsonMsg("Aucne réservation de salle n'a été trouvée avec cet ID."));
        return $response;
    }

    private function jsonMsg($text) {
        return '{ "message": "'.$text.'" }';
    }
}