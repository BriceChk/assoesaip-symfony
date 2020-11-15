<?php


namespace App\Controller;


use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class API_SearchController extends AbstractFOSRestController {

    /**
     * Search a user by its name or email.
     * @Rest\Get(
     *     path = "/api/user/search/{term}",
     *     name = "api_user_search"
     * )
     * @View
     * @IsGranted("ROLE_USER")
     * @param string $term
     * @return User[]
     */
    public function searchUser(string $term) {
        $rep = $this->getDoctrine()->getRepository(User::class);
        return $rep->search($term);
    }

}