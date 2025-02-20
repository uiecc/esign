<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;



class LoginAuthenticatiorAuthenticator extends AbstractLoginFormAuthenticator implements AuthenticationFailureHandlerInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private $session;

    public function __construct(private UrlGeneratorInterface $urlGenerator, private UserProviderInterface $userProvider) {}

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token');

        if (empty($email)) {
            throw new CustomUserMessageAuthenticationException('login.error.email_required');
        }

        if (empty($password)) {
            throw new CustomUserMessageAuthenticationException('login.error.password_required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AuthenticationException('invalid_email');
        }
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function ($userIdentifier) {
                return $this->userProvider->loadUserByIdentifier($userIdentifier);
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        if ($user->hasRegistered()) {
            $candidate = $user->getCandidate();
            return new RedirectResponse($this->urlGenerator->generate('app_candidate_preview', ['id' => $user->getCandidate()->getId(), 'examinationLanguage' => $candidate->getLanguage()]));
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Rediriger vers la d'inscription après une connexion réussie
        return new RedirectResponse($this->urlGenerator->generate('app_login_status', ['success' => true]));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {

        if ($request->isXmlHttpRequest()) {
            $errorMessage = $exception->getMessage();
            return new JsonResponse([
                'error' => $errorMessage === 'invalid_email' ? 'invalid_email' : 'invalid_credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        if ($exception instanceof CustomUserMessageAuthenticationException) {
            $errorMessage = $exception->getMessageKey();
        } else {
            $errorMessage = 'Invalid credentials.';
        }
        
        $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $errorMessage);
        
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
