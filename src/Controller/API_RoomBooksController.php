<?php

namespace App\Controller;

use App\Entity\AssoEsaipSettings;
use App\Entity\Project;
use App\Entity\RoomBook;
use App\Utils;
use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use phpDocumentor\Reflection\Types\Integer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class API_RoomBooksController extends AbstractFOSRestController
{
    //TODO Serializer groups

    /**
     * Get a RoomBook from its id. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the requested RoomBook",
     *     @OA\JsonContent(ref=@Model(type=RoomBook::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The RoomBook unique identifier",
     *     @OA\Schema(type="integer")
     * )
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
        $response = new Response();
        $rep = $this->getDoctrine()->getRepository(RoomBook::class);
        $rb = $rep->find($id);
        if ($rb == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune réservation de salle trouvée avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $rb->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        return $rb;
    }

    /**
     * Get all RoomBooks of a Project from its id. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The list of RoomBooks",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=RoomBook::class)))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Project doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Project unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @Rest\Get(
     *     path = "/api/project/{id}/roombooks",
     *     name = "api_roombook_show_all",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RoomBook")
     * @View
     * @IsGranted("ROLE_USER")
     * @param Integer $id The RoomBooks ID
     * @return RoomBook[]|Response
     */
    public function showAllRoombooks($id) {
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

        return $project->getRoomBooks();
    }

    /**
     * Create a new RoomBook. The user must be a Project admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created RoomBook",
     *     @OA\JsonContent(ref=@Model(type=RoomBook::class))
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
     *     description="The RoomBook as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=RoomBook::class))
     * )
     * @Rest\Put(
     *     path = "/api/project/{id}/roombooks",
     *     name = "api_roombook_create"
     * )
     * @OA\Tag(name="RoomBook")
     * @View(statusCode=201)
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param MailerInterface $mailer
     * @return RoomBook|\FOS\RestBundle\View\View|Response
     */
    public function newRoombook($id, Request $request, ValidatorInterface $validator, MailerInterface $mailer) {
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

        $rb = new RoomBook();
        $rb->setProject($project);
        $rb->setUser($this->getUser());
        $rb->setObject($json['object']);
        $rb->setNeeds($json['needs']);
        $rb->setNbParticipants($json['nb_participants']);
        try {
            $rb->setStartTime(new DateTime($json['start_time']));
            $rb->setEndTime(new DateTime($json['end_time']));
            $rb->setDate(new DateTime($json['date']));
        } catch (\Exception $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Le format de l'heure ou de la date est invalide."));
            return $response;
        }

        $errors = $validator->validate($rb);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($rb);
        $em->flush();

        $settRep = $this->getDoctrine()->getRepository(AssoEsaipSettings::class);
        $mailsList = $settRep->getRoombooksRecipients();

        if (count($mailsList) != 0) {
            $email = (new TemplatedEmail())
                ->from('asso@esaip.org')
                ->to(...$mailsList)
                ->subject('Nouvelle demande de réservation de salle')
                ->htmlTemplate('emails/roombook_new.html.twig')
                ->context([
                    'rb' => $rb
                ]);

            $email->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                error_log($e->getMessage());
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                $response->setContent(Utils::jsonMsg("Erreur interne (email). La demande a quand même été enregistrée mais sera peut être ignorée."));
                return $response;
            }
        }

        return $rb;
    }

    /**
     * Update (answer) a RoomBook. The user must be a site admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested RoomBook has been modified",
     *     @OA\JsonContent(ref=@Model(type=RoomBook::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested RoomBook doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a site admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The RoomBook unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="The RoomBook as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=RoomBook::class))
     * )
     * @Rest\Post(
     *     path = "/api/roombook/{id}",
     *     name = "api_roombook_update",
     *     requirements = { "id"="\d+" }
     * )
     * @OA\Tag(name="RoomBook")
     * @View()
     * @IsGranted("ROLE_USER")
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param MailerInterface $mailer
     * @return RoomBook|object|Response
     */
    public function updateRoombook($id, Request $request, ValidatorInterface $validator, MailerInterface $mailer) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(RoomBook::class);
        $rb = $rep->find($id);

        if ($rb == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune réservation de salle trouvée avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur du site."));
            return $response;
        }

        $json = $request->request->all();
        $rb->setStatus($json['status']);
        $rb->setRoom($json['room']);

        $errors = $validator->validate($rb);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($rb);
        $em->flush();

        $email = (new TemplatedEmail())
            ->from('asso@esaip.org')
            ->to($rb->getUser()->getEmail())
            ->subject('Réservation de salle ' . strtolower($rb->getStatus()))
            ->htmlTemplate('emails/roombook_response.html.twig')
            ->context([
                'rb' => $rb
            ]);

        $email->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            error_log($e->getMessage());
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(Utils::jsonMsg("Erreur interne (email). Le reponsable ne sera pas prévenu."));
            return $response;
        }

        return $rb;
    }

    /**
     * Delete a RoomBook. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested RoomBook has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested RoomBook doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The RoomBook unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="RoomBook")
     * @Rest\Delete(
     *     path = "/api/roombook/{id}",
     *     name = "api_roombook_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return Response
     */
    public function deleteRb($id) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(RoomBook::class);
        $rb = $rep->find($id);

        if ($rb == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune réservation de salle trouvée avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $rb->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($rb);
        $em->flush();

        $response->setContent(Utils::jsonMsg("La réservation de salle a été supprimée."));
        return $response;
    }
}