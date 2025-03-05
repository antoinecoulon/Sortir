<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class CustomAuthenticator extends AbstractAuthenticator
{
    private UserRepository $userRepository;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UserRepository $userRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->request->has('email_or_pseudo');
    }

    /**
     * When user log in, get the inputs if exists, then use user repository to check in database if one input is an
     * existing pseudo or email and the other input is a password to grant an access and verify csrf token to remember
     * user during  one week
     * @param Request $request
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $emailOrPseudo = $request->request->get('email_or_pseudo');
        $password = $request->request->get('_password');

        if (!$emailOrPseudo || !$password) {
            throw new CustomUserMessageAuthenticationException('Veuillez entrer vos identifiants.');
        }

        return new Passport(
           new UserBadge($emailOrPseudo, function ($userIdentifier) {
               $user = $this->userRepository->loadUserByIdentifier($userIdentifier);
               if (!$user) {
                   throw new UserNotFoundException('Utilisateur non trouvé');
               }

               if (!$user->isVerified()) {
                   throw new CustomUserMessageAuthenticationException('Veuillez vérifier votre adresse email avant de vous connecter.');
               }

               if (!$user->isActive()) {
                   throw new CustomUserMessageAccountStatusException("Votre compte à été désactivé !  Veuillez vous rapprocher d'un administrateur pour en connaitre la raison.");
               }

               return $user;

           }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
                new RememberMeBadge()
            ]

       );
    }

    /**
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return new RedirectResponse($this->urlGenerator->generate('app_event'));
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Vérifiez si l'exception est liée à l'email non vérifié
        if ($exception instanceof CustomUserMessageAuthenticationException &&
            $exception->getMessage() === 'Veuillez vérifier votre adresse email avant de vous connecter.') {
            $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        if ($exception instanceof CustomUserMessageAccountStatusException &&
            $exception->getMessage() === "Votre compte à été désactivé !  Veuillez vous rapprocher d'un administrateur pour en connaitre la raison.") {
            $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }


        $request->getSession()->getFlashBag()->add('error', 'Identifiants incorrects.');

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    // public function start(Request $request, ?AuthenticationException $authException = null): Response
    // {
    //     /*
    //      * If you would like this class to control what happens when an anonymous user accesses a
    //      * protected page (e.g. redirect to /login), uncomment this method and make this class
    //      * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //      *
    //      * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //      */
    // }
}
