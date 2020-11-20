<?php
namespace App\Security;

use App\Entity\User;
use App\Utils;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\AzureClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface; // your user entity

class AzureAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $em;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_azure_check';
    }

    public function getCredentials(Request $request)
    {
        // this method is only called if supports() returns true
        return $this->fetchAccessToken($this->getAzureClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $azureUser = $this->getAzureClient()->fetchUserFromToken($credentials);

        // 1) have they logged in with Azure before? Easy!
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['username' => $azureUser->getId()]);

        if ($existingUser) {
            return $existingUser;
        }

        if (strpos($azureUser->getUpn(), '@esaip.org') === false) {
            return null;
        }


        // 3) Register new user
        $user = new User();
        $user->setUsername($azureUser->getId());
        $user->setEmail($azureUser->getUpn());
        $user->setFirstName($azureUser->getFirstName());
        $user->setLastName($azureUser->getLastName());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @return AzureClient
     */
    private function getAzureClient()
    {
        return $this->clientRegistry->getClient('azure');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        $homeUrl = $this->router->generate('home');
        $session = new Session();

        // Redirect to last page
        $targetUrl = $session->get('redirect_uri', $homeUrl);
        $session->remove('redirect_uri');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        if ($exception instanceof UsernameNotFoundException) {
            //TODO Page erreur compte qui ne contient pas @esaip.org
        }

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse|Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // JSON Response for API calls
        if (str_contains($request->getUri(), 'api')) {
            $r = new Response();
            $r->setStatusCode(Response::HTTP_UNAUTHORIZED);
            $r->setContent(Utils::jsonMsg("Vous n'êtes pas connecté."));
            return $r;
        }

        // Save current URL in session to redirect later
        $session = new Session();
        $uri = $request->getUri();
        $session->set('redirect_uri', $uri);

        return new RedirectResponse(
            '/login-office/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

// ...
}