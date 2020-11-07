<?php

namespace App\Controller;

use App\Entity\AENews;
use DateTime;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class API_AENewsController extends AbstractController
{

    /**
     * @OA\Response () (
     *     response = 200,
     *     description = "Returns an Asso'esaip News",
     *     @Model(type=AENews::class)
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="query",
     *     description="The AENews unique identifier",
     *     @OA\Schema(type="string")
     * )
     * Get a AENews from its id.
     * @Rest\Get(
     *     path = "/api/assoesaip-news/{id}",
     *     name = "api_ae_news_show",
     *     requirements = { "id"="\d+" }
     * )
     * @View
     * @IsGranted("ROLE_USER")
     * @param $id integer The AENews unique identifier.
     * @return AENews|Response
     */
    public function showAENews(int $id) {
        $em = $this->getDoctrine()->getRepository(AENews::class);
        $aeNews = $em->find($id);
        if ($aeNews == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($this->jsonMsg("Aucune News Asso'esaip n'a été trouvée avec cet ID."));
            return $response;
        }
        return $aeNews;
    }

    /**
     * @Rest\Put(
     *     path = "/api/assoesaip-news",
     *     name = "api_ae_news_create"
     * )
     * @View(statusCode=201)
     * @ParamConverter("aeNews", converter="fos_rest.request_body")
     * @IsGranted("ROLE_ADMIN")
     */
    public function newAENews(AENews $aeNews)
    {
        $aeNews->setDatePublished(new DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($aeNews);
        $em->flush();

        if ($em->contains($aeNews)) {
            return $aeNews;
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setContent($this->jsonMsg("Une erreur s'est produite lors de l'enregistrement."));
        return $response;
    }

    /**
     * @Rest\Post(
     *     path = "/api/assoesaip-news/{id}",
     *     name = "api_ae_news_update",
     *     requirements = { "id"="\d+" }
     * )
     * @View(statusCode=201)
     * @ParamConverter("updatedNews", converter="fos_rest.request_body")
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateAENews(AENews $updatedNews, $id) {
        $rep = $this->getDoctrine()->getRepository(AENews::class);
        $news = $rep->find($id);
        if ($news == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent($this->jsonMsg("Aucune News Asso'esaip trouvée avec cet ID."));
        }

        $news->setHasHTML($updatedNews->getHasHTML());
        $news->setContent($updatedNews->getContent());
        $news->setLink($updatedNews->getLink());
        $news->setTitle($updatedNews->getTitle());
        $news->setHtml($updatedNews->getHtml());

        if ($updatedNews->getPublished() && $updatedNews->getPublished() != $news->getPublished()) {
            $news->setDatePublished(new DateTime());
        }

        $news->setPublished($updatedNews->getPublished());

        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();

        return $news;
    }

    /**
     * @Rest\Delete(
     *     path = "/api/assoesaip-news/{id}",
     *     name = "api_ae_news_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @View(statusCode=200)
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteAENews(AENews $aeNews) {
        $response = new Response();

        $em = $this->getDoctrine()->getManager();
        if ($em->contains($aeNews)) {
            $em->remove($aeNews);
            $em->flush();
            $response->setStatusCode(Response::HTTP_OK);
            $response->setContent($this->jsonMsg("La News Asso'esaip a été supprimée."));
            return $response;
        }
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $response->setContent($this->jsonMsg("Aucne News Asso'esaip n'a été trouvée avec cet ID."));
        return $response;
    }

    private function jsonMsg($text) {
        return '{ "message": "'.$text.'" }';
    }
}