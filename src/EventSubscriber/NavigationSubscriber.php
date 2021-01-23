<?php

namespace App\EventSubscriber;

use App\Entity\AssoEsaipSettings;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NavigationSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $em;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $em
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    public function onRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // Maintenance mode
        $setRepo = $this->em->getRepository(AssoEsaipSettings::class);
        if ($setRepo->isMaintenanceModeEnabled()) {
            $route = $event->getRequest()->attributes->get('_route');
            $token = $this->tokenStorage->getToken();
            $user = $token == null ? null : $token->getUser();
            if ($route == 'connect_azure_start' || $route == 'connect_azure_check' || $route == 'maintenance' || ($user != null && in_array('ROLE_ADMIN', $user->getRoles()))) {
                return;
            }
            $event->setResponse(new RedirectResponse('maintenance'));
            return;
        }

        if (!$token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!$token->isAuthenticated()) {
            return;
        }

        /** @var User $user */
        if (!$user = $token->getUser()) {
            return;
        }

        $user->updateLastLogin();
        $this->em->persist($user);
        $this->em->flush();

        if ($event->getRequest()->attributes->get('_route') == 'profile') {
            return;
        }

        // Redirect to profile page if needed
        if ($user->getPromo() == '' || $user->getCampus() == '') {
            $event->setResponse(new RedirectResponse('profil'));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}
