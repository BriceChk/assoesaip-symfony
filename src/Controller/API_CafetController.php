<?php
namespace App\Controller;

use App\Entity\AssoEsaipSettings;
use App\Entity\CafetItem;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class API_CafetController extends AbstractFOSRestController {

    /**
     * Get the list of CafetItems.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the CafetItem list",
     *     @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=CafetItem::class)))
     * )
     * @OA\Tag(name="Cafet")
     * @Rest\Get(
     *     path = "/api/cafet/",
     *     name = "api_cafet_list",
     *     requirements = { "id"="\d+" }
     * )
     * @View
     * @IsGranted("ROLE_USER")
     * @return CafetItem[]|object[]
     */
    public function getCafetItemsList(): array {
        $rep = $this->getDoctrine()->getRepository(CafetItem::class);
        $items = $rep->findAll();

        $settingsRep = $this->getDoctrine()->getRepository(AssoEsaipSettings::class);
        $open = $settingsRep->isCafetOpen();
        $message = $settingsRep->getCafetMessage();

        return [
            'items' => $items,
            'is_open' => $open,
            'message' => $message
        ];
    }
}