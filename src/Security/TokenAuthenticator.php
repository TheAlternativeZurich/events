<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * TokenAuthenticator constructor.
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function supports(Request $request): ?bool
    {
        return false;
    }

    public function authenticate(Request $request): PassportInterface
    {
        throw new MethodNotImplementedException(__FUNCTION__);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        throw new MethodNotImplementedException(__FUNCTION__);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new MethodNotImplementedException(__FUNCTION__);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->generator->generate('create'));
    }

    public function getCredentials(Request $request)
    {
        throw new MethodNotImplementedException(__FUNCTION__);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        throw new MethodNotImplementedException(__FUNCTION__);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        throw new MethodNotImplementedException(__FUNCTION__);
    }

    public function supportsRememberMe()
    {
        return true;
    }
}
