<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Base\BaseFormController;
use App\Entity\User;
use App\Security\UserToken;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends BaseFormController
{
    /**
     * @Route("/authenticate/{authenticationHash}", name="authenticate")
     */
    public function login(string $authenticationHash, Request $request, GuardAuthenticatorHandler $guardHandler, TranslatorInterface $translator): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findOneBy(['authenticationHash' => $authenticationHash]);

        if (null !== $user) {
            $userToken = new UserToken($user);
            $guardHandler->authenticateWithToken($userToken, $request, 'main');

            $message = $translator->trans('authenticate.success.authentication_successful', [], 'security');
            $this->displaySuccess($message);
        } else {
            $message = $translator->trans('authenticate.errors.authentication_code_invalid', [], 'security');
            $this->displayError($message);
        }

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
