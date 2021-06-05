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
     * @OA\Tag(name="ProjectPage")
     * @Rest\Post(
     *     path = "/api/prevention-page/{id}",
     *     name = "api_prevention_page_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_ADMIN")
     * @View()
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param \HTMLPurifier $purifier
     * @param UploadHandler $handler
     * @return ProjectPage|\FOS\RestBundle\View\View|object|Response
     */
    /*
    public function updatePage($id, Request $request, ValidatorInterface $validator, \HTMLPurifier $purifier, UploadHandler $handler)
    {
        $response = new Response();

        $doc = $this->getDoctrine();
        $settingsRep = $doc->getRepository(AssoEsaipSettings::class);

        $description = $settingsRep->getPresentationPolePrevention();

        if (!$this->isGranted('ROLE_ADMIN')) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur."));
            return $response;
        }



        return $page;
    }*/
}
