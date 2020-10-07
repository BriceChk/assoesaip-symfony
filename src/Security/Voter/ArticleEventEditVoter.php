<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleEventEditVoter extends Voter
{
    private $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    protected function supports($attribute, $subject) {
        return $attribute === 'EDIT' && ($subject instanceof Article || $subject instanceof Event);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        // on retrouve l'utilisateur (on peut aussi ré-utiliser $this->security)
        /** @var User $user */
        $user = $token->getUser();

        // si l'utilisateur n'est pas authentifié, c'est non!
        if (!$user instanceof UserInterface) {
            return false;
        }

        // l'utilisateur est l'auteur de l'article / événement
        if ($user === $subject->getAuthor()) {
            return true;
        }

        // l'utilisateur a le role d'admin sur le projet
        $project = $subject->getProject();
        if ($this->security->isGranted('PROJECT_ADMIN', $project)) {
            return true;
        }

        // l'utilisateur est un administrateur
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }
}
