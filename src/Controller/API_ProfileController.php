<?php


namespace App\Controller;


use App\Entity\FcmToken;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class API_ProfileController extends AbstractFOSRestController {

    /**
     * Show the current user's profile
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the current User's profile",
     *     @OA\JsonContent(ref=@Model(type=User::class))
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "You are not logged in to the website."
     * )
     * @Rest\Get(
     *     path = "/api/profile",
     *     name = "api_profile_show",
     * )
     * @View()
     * @OA\Tag(name="User")
     * @IsGranted("ROLE_USER")
     * @return User
     */
    public function showProfile(): User {
        /** @var User $user */
        $user = $this->getUser();
        return $user;
    }

    /**
     * Update the current users's profile
     * @OA\Response (
     *     response = 200,
     *     description = "Returns the updated User's profile",
     *     @OA\JsonContent(ref=@Model(type=User::class))
     * )
     * @OA\Response (
     *     response = 403,
     *     description = "You are not logged in to the website."
     * )
     * @OA\RequestBody(
     *     description="The User as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=User::class, groups={"profileEdit"}))
     * )
     * @Rest\Post(
     *     path = "/api/profile",
     *     name = "api_profile_update",
     * )
     * @OA\Tag(name="User")
     * @IsGranted("ROLE_USER")
     * @View(serializerGroups={"profile"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return User|\FOS\RestBundle\View\View
     */
    public function updateProfile(Request $request, ValidatorInterface $validator) {
        /** @var User $user */
        $user = $this->getUser();

        $json = $request->request->all();
        $user->setPromo($json['promo']);
        $user->setCampus($json['campus']);

        $errors = $validator->validate($user);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Save a Firebase Cloud Messaging token for the current User.
     * @OA\Response (
     *     response = 200,
     *     description = "The token has been saved",
     *     @OA\JsonContent(ref=@Model(type=FcmToken::class))
     * )
     * @OA\RequestBody(
     *     description="The FcmToken as a JSON object",
     *     @OA\JsonContent(ref=@Model(type=FcmToken::class, groups={"fcmtoken"}))
     * )
     * @Rest\Post(
     *     path = "/api/profile/fcm-token",
     *     name = "api_profile_save_fcm_token",
     * )
     * @View
     * @OA\Tag(name="User")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return FcmToken|\FOS\RestBundle\View\View
     */
    public function saveFcmToken(Request $request, ValidatorInterface $validator) {
        /** @var User $user */
        $user = $this->getUser();

        $json = $request->request->all();
        $rep = $this->getDoctrine()->getRepository(FcmToken::class);
        $token = $rep->findOneBy(['user' => $user, 'instanceId' => $json['instance_id']]);
        if ($token == null) {
            $token = new FcmToken();
            $token->setUser($user);
            $token->setInstanceId($json['instance_id']);
        }
        $token->setToken($json['token']);
        if ($json['notifications_enabled'] != null) {
            $token->setNotificationsEnabled($json['notifications_enabled']);
        }

        $errors = $validator->validate($token);
        if (count($errors)) {
            return $this->view($errors, Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($token);
        $em->flush();

        return $token;
    }

}