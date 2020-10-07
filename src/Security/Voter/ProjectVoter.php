<?php

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectVoter extends Voter {
    private $security;

    const ADMIN = 'PROJECT_ADMIN';

    public function __construct(Security $security) {
        $this->security = $security;
    }

    protected function supports($attribute, $subject) {
        if (!in_array($attribute, [self::ADMIN])) {
            return false;
        }

        if (!$subject instanceof Project) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        // on retrouve l'utilisateur (on peut aussi ré-utiliser $this->security)

        /** @var User $user */
        $user = $token->getUser();

        // si l'utilisateur n'est pas authentifié, c'est non!
        if (!$user instanceof UserInterface) {
            return false;
        }

        // l'utilisateur est un administrateur
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Project $project */
        $project = $subject;

        switch ($attribute) {
            case self::ADMIN:
                return $this->isAdmin($project, $user);
        }

        return false;
    }

    /**
     * @param Project $project
     * @param User $user
     * @return bool
     */
    private function isAdmin($project, $user) {
        foreach ($project->getMembers() as $member) {
            if ($member->getUser() === $user && $member->isAdmin()) {
                return true;
            }
        }
        return false;
    }
}

