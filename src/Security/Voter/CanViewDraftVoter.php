<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\Event;
use App\Entity\ProjectMember;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CanViewDraftVoter extends Voter
{
    private $security;
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(Security $security, EntityManagerInterface $em) {
        $this->security = $security;
        $this->em = $em;
    }

    protected function supports($attribute, $subject) {
        return $attribute === 'ROLE_PROJECT_EDITOR';
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        // This Voter checks if the user is editor on at least one projet, allowing him to see draft events and
        // access "Espace assos" pages.

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // User is admin
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Checks if the user is editor of at least one project
        $rep = $this->em->getRepository(ProjectMember::class);
        $rep->findOneBy(["user" => $user, "admin" => true]);

        if ($rep instanceof ProjectMember) {
            return true;
        }

        return false;
    }
}
