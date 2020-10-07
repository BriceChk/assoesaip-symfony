<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profil", name="profile")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $updatedUser */
            $updatedUser = $form->getData();

            $user->setCampus($updatedUser->getCampus());
            $user->setPromo($updatedUser->getPromo());
            $user->setAvatarFile($updatedUser->getAvatarFile());

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $user->setAvatarFile(null);
            $this->addFlash('success', 'Profil mis à jour ! Vous pouvez continuer la navigation.');
        }

        return $this->render('profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/profil/delete_pic", name="profile_pic_delete")
     * @IsGranted("ROLE_USER")
     */
    public function deleteProfilePic(UploadHandler $handler, CacheManager $cacheManager, UploaderHelper $uploaderHelper) {
        /** @var User $user */
        $user = $this->getUser();
        // Removing cached image
        $cacheManager->remove($uploaderHelper->asset($user, 'avatarFile'));
        // Removing stored image
        $handler->remove($user, 'avatarFile');

        $user->setAvatarFile(null);
        $user->setAvatarFileName('');

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Photo de profil supprimée.');
        return $this->redirectToRoute('profile');
    }
}
