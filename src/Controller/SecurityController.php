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

use App\Controller\Base\BaseDoctrineController;
use App\Entity\User;
use App\Form\User\LoginType;
use App\Form\User\RegisterType;
use App\Helper\HashHelper;
use App\Security\UserToken;
use App\Service\Interfaces\EmailServiceInterface;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends BaseDoctrineController
{
    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, GuardAuthenticatorHandler $guardHandler, TranslatorInterface $translator): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user)
            ->add('submit', SubmitType::class, ['translation_domain' => 'security', 'label' => 'create.submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $message = $translator->trans('create.error.already_registered', [], 'security');
                $this->displayError($message);

                return $this->redirectToRoute('authenticate');
            }

            $this->fastSave($user);

            $message = $translator->trans('create.success.welcome', [], 'security');
            $this->displaySuccess($message);

            return $this->loginAndRedirect($user, $guardHandler, $request);
        }

        return $this->render('security/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/authenticate/{authenticationHash}", defaults={"authenticationHash"=null}, name="authenticate")
     */
    public function login(?string $authenticationHash, Request $request, GuardAuthenticatorHandler $guardHandler, EmailServiceInterface $emailService, TranslatorInterface $translator): Response
    {
        if (null !== $authenticationHash && HashHelper::HASH_LENGTH === mb_strlen($authenticationHash)) {
            $response = $this->tryLogin($authenticationHash, $guardHandler, $request, $translator);
            if ($response instanceof Response) {
                return $response;
            }
        }

        if (null !== $this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $user = new User();
        $form = $this->createForm(LoginType::class, $user)
            ->add('submit', SubmitType::class, ['translation_domain' => 'security', 'label' => 'authenticate.submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->sendAuthenticationEmail($user, $emailService, $translator);
        }

        return $this->render('security/authenticate.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function sendAuthenticationEmail(User $user, EmailServiceInterface $emailService, TranslatorInterface $translator): void
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser && !$emailService->sendAuthenticateLink($existingUser)) {
            $message = $translator->trans('errors.email_could_not_be_sent', [], 'email');
            $this->displayError($message);
        } else {
            // also show success message if user is not found to make it intransparent who is registered
            $message = $translator->trans('authenticate.success.sent_authentication_link', [], 'security');
            $this->displaySuccess($message);
        }
    }

    private function tryLogin(string $authenticationHash, GuardAuthenticatorHandler $guardHandler, Request $request, TranslatorInterface $translator): ?RedirectResponse
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findOneBy(['authenticationHash' => $authenticationHash]);

        if (null !== $user) {
            $message = $translator->trans('authenticate.success.authentication_successful', [], 'security');
            $this->displaySuccess($message);

            return $this->loginAndRedirect($user, $guardHandler, $request);
        } else {
            $message = $translator->trans('authenticate.errors.authentication_code_invalid', [], 'security');
            $this->displayError($message);

            return null;
        }
    }

    private function loginAndRedirect(User $user, GuardAuthenticatorHandler $guardHandler, Request $request): RedirectResponse
    {
        $userToken = new UserToken($user);
        $guardHandler->authenticateWithToken($userToken, $request, 'main');

        $redirectPathKey = '_security.main.target_path';
        if ($request->getSession()->has($redirectPathKey)) {
            $value = $request->getSession()->get($redirectPathKey);
            $request->getSession()->remove($redirectPathKey);

            return $this->redirect($value);
        }

        return $this->redirectToRoute('index');
    }
}
