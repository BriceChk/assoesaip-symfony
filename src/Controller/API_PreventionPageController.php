<?php

namespace App\Controller;

use App\Entity\AssoEsaipSettings;
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

class API_PreventionPageController extends AbstractFOSRestController
{

    /**
     * Update the PreventionPage. The user must be an admin or prevention member.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested ProjectPage has been modified",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested PreventionPage doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not an admin or prevention member"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The PreventionPage unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="The PreventionPage as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
     * )
     * @OA\Tag(name="PreventionPage")
     * @Rest\Post(
     *     path = "/api/prevention-page",
     *     name = "api_prevention_page_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View()
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param \HTMLPurifier $purifier
     * @param UploadHandler $handler
     * @return ProjectPage|\FOS\RestBundle\View\View|object|Response
     */

    public function updatePage(Request $request, ValidatorInterface $validator, \HTMLPurifier $purifier, UploadHandler $handler)
    {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        $doc = $this->getDoctrine();
        $settingsRep = $doc->getRepository(AssoEsaipSettings::class);

        $description = $settingsRep->getPresentationPolePrevention();

        if ($description == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune description n'a été remplie"));
            return $response;
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas membre du pôle Prévention."));
            return $response;
        }

        $json = $request->request->all();

        $description->setPublished($json['published']);
        $description->setHtml($purifier->purify($json['html']));
        $description->setName($json['name']);
        $description->setOrderPosition($json['order_position']);

        $errors = $validator->validate($description);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($description);
        $em->flush();

        return $description;
    }

    /**
     * Get a ProjectPage from its ID.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the requested PreventionPage",
     *     @OA\JsonContent(ref=@Model(type=ProjectPage::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested PreventionPage doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The PreventionPage unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="PreventionPage")
     * @Rest\Get(
     *     path = "/api/prevention-page/{id}",
     *     name = "api_prevention_page_show",
     *     requirements = { "id"="\d+" }
     * )
     * @View(serializerGroups={"basic", "page-full"})
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function showPage() {
        $settingsRep = $this->getDoctrine()->getRepository(AssoEsaipSettings::class);
        $description = $settingsRep->getPresentationPolePrevention();

        return $description;
    }
}
