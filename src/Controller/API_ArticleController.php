<?php


namespace App\Controller;


use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\FcmToken;
use App\Entity\News;
use App\Entity\Project;
use App\Utils;
use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use HTMLPurifier;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//TODO Image upload + Discord integration + notifications

class API_ArticleController extends AbstractFOSRestController {
    /**
     * Get an Article from its ID.
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the requested Article",
     *     @OA\JsonContent(ref=@Model(type=Article::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Article doesn't exist"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Article unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Article")
     * @Rest\Get(
     *     path = "/api/article/{id}",
     *     name = "api_article_show",
     *     requirements = { "id"="\d+" }
     * )
     * @View(serializerGroups={"basic", "page-full"})
     * @IsGranted("ROLE_USER")
     * @param Integer $id The Article ID
     * @return Article|Response
     */
    public function showArticle(int $id) {
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);
        $article = $articleRepo->find($id);

        if ($article == null) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun article n'a été trouvée avec cet ID."));
            return $response;
        }

        return $article;
    }

    /**
     * Create a new Article. The user must be a Project admin.
     * @OA\Response (
     *     response = 201,
     *     description = "Returns the created Article",
     *     @OA\JsonContent(ref=@Model(type=Article::class))
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
     *     description="The Article as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=Article::class))
     * )
     * @OA\Tag(name="Article")
     * @Rest\Put(
     *     path = "/api/project/{id}/articles",
     *     name = "api_project_article_create",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @View(statusCode=201)
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param HTMLPurifier $purifier
     * @return Article|\FOS\RestBundle\View\View|Response
     */
    public function newPage($id, Request $request, ValidatorInterface $validator, HTMLPurifier $purifier) {
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

        $article = new Article();
        $json = $request->request->all();

        $cat = $this->getDoctrine()->getRepository(ArticleCategory::class)->find($json['category']);
        if ($cat == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie trouvée avec cet ID."));
            return $response;
        }
        $article->setCategory($cat);

        $article->setTitle($json['title']);
        $article->setAuthor($this->getUser());
        $article->setProject($project);
        $article->setAbstract(trim($json['abstract']));
        $article->setHtml($purifier->purify($json['html']));
        $article->setDateCreated(new DateTime('now'));
        $article->setDateEdited(new DateTime('now'));
        $article->setPublished(false);
        $article->setPrivate($json['private']);

        $errors = $validator->validate($article);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $article;
    }

    /**
     * Delete an Article. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested Article has been deleted"
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Article doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Article unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Article")
     * @Rest\Delete(
     *     path = "/api/article/{id}",
     *     name = "api_article_delete",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @param $id
     * @return Response
     */
    public function deleteArticle($id) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(Article::class);
        $article = $rep->find($id);

        if ($article == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun article trouvé avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $article->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();

        $response->setContent(Utils::jsonMsg("L'article a été supprimé."));
        return $response;
    }

    /**
     * Update an Article. The user must be a Project admin.
     * @OA\Response (
     *     response = 200,
     *     description = "The requested Article has been modified",
     *     @OA\JsonContent(ref=@Model(type=Article::class))
     * )
     * @OA\Response (
     *     response = 404,
     *     description = "The requested Article doesn't exist"
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "The user is not a Project admin"
     * )
     * @OA\Parameter (
     *     name = "id",
     *     in="path",
     *     description="The Article unique identifier",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Article")
     * @Rest\Post(
     *     path = "/api/article/{id}",
     *     name = "api_article_update",
     *     requirements = { "id"="\d+" }
     * )
     * @IsGranted("ROLE_USER")
     * @View()
     * @param $id
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param HTMLPurifier $purifier
     * @param SluggerInterface $slugger
     * @param Messaging $messaging
     * @return Article|\FOS\RestBundle\View\View|object|Response
     */
    public function updateArticle($id, Request $request, ValidatorInterface $validator, HTMLPurifier $purifier, SluggerInterface $slugger, Messaging $messaging) {
        $response = new Response();

        $rep = $this->getDoctrine()->getRepository(Article::class);
        $article = $rep->find($id);

        if ($article == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucun article trouvé avec cet ID."));
            return $response;
        }

        if (!$this->isGranted('PROJECT_ADMIN', $article->getProject())) {
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $response->setContent(Utils::jsonMsg("Vous n'êtes pas administrateur de ce projet."));
            return $response;
        }

        $json = $request->request->all();

        $cat = $this->getDoctrine()->getRepository(ArticleCategory::class)->find($json['category']);
        if ($cat == null) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(Utils::jsonMsg("Aucune catégorie trouvée avec cet ID."));
            return $response;
        }
        $article->setCategory($cat);

        $article->setTitle($json['title']);
        $article->setAbstract(trim($json['abstract']));
        $article->setHtml($purifier->purify($json['html']));
        $article->setDateEdited(new DateTime('now'));

        $publishNews = false;

        if (!$json['published']) {
            $article->setUrl(''); // L'article n'est pas publié : on enlève l'url
        } elseif ($article->isPrivate() && !$json['private']) {
            $publishNews = true; // L'article est publié et passe de privé à public : on publie la news
        }

        $article->setPrivate($json['private']);

        if (!$article->isPublished() && $json['published']) {
            // L'article passe de brouillon à publié : on crée l'url
            $article->setAuthor($this->getUser());
            $article->setDatePublished(new DateTime('now'));

            $slug = $slugger->slug($json['title']);
            $test = $rep->findBy(['url' => $slug]);
            while (count($test) != 0 && $test == $article->getProject()) {
                $slug .= '-1';
                $test = $rep->findBy(['url' => $slug]);
            }

            $article->setUrl($slug);

            if (!$article->isPrivate()) {
                $publishNews = true; // Si il n'est pas privé, on publie la news
            }
        }
        $article->setPublished($json['published']);

        $em = $this->getDoctrine()->getManager();

        // On enlève l'éventuelle news de l'article si l'article n'est pas publié ou en privé
        if (!$article->isPublished() || $article->isPrivate()) {
            $nRep = $this->getDoctrine()->getRepository(News::class);
            $oldNews = $nRep->findOneBy(['article' => $article]);
            if ($oldNews != null) {
                $em->remove($oldNews);
            }
        }

        $errors = $validator->validate($article);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($article);

        if ($publishNews) {
            $news = new News();
            $news->setArticle($article);
            $news->setDatePublished($article->getDatePublished());
            $news->setProject($article->getProject());
            $em->persist($news);

            /* TODO notifications
            $rep = $this->getDoctrine()->getRepository(FcmToken::class);
            $all = $rep->findAll();

            foreach ($all as $a) {
                $message = CloudMessage::withTarget('token', $a->getToken())
                    ->withData(['type' => 'article', 'id' => $article->getId()]);
                $messaging->send($message);
            }
            */
        }

        $em->flush();

        return $article;
    }
}