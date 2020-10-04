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
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use App\Form\Registration\EditType;
use App\Security\UserToken;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/")
 */
class IndexController extends BaseDoctrineController
{
    /**
     * @Route("", name="index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $registrations = $this->getUser()->getRegistrations();

        /** @var Registration[] $upcomingRegistrations */
        $upcomingRegistrations = [];
        /** @var Registration[] $pastRegistrations */
        $pastRegistrations = [];
        foreach ($registrations as $registration) {
            $event = $registration->getEvent();

            $key = $event->getStartDate()->format('c').'_'.$event->getId();
            if (null === $event->getClosedDate()) {
                $upcomingRegistrations[$key] = $registration;
            } else {
                $pastRegistrations[$key] = $registration;
            }
        }

        krsort($upcomingRegistrations);
        krsort($pastRegistrations);

        return $this->render('index.html.twig', ['upcoming_registrations' => $upcomingRegistrations, 'past_registrations' => $pastRegistrations]);
    }

    /**
     * @Route("e/{identifier}", name="register", priority="-10")
     *
     * @return Response
     */
    public function registerAction(string $identifier, Request $request, GuardAuthenticatorHandler $guardHandler, EmailServiceInterface $emailService, TranslatorInterface $translator)
    {
        /** @var Event $event */
        $event = $this->getDoctrine()->getRepository(Event::class)->findOneBy(['identifier' => $identifier]);
        if (null === $event) {
            throw new NotFoundHttpException();
        }

        $user = $this->getUser();

        /** @var Registration|null $existingRegistration */
        $existingRegistration = null;
        if ($user) {
            $existingRegistration = $user->getRegistrationFor($event);
        }

        $organizerSecretValid = $request->query->has('organizer-secret') &&
            $request->query->get('organizer-secret') === $event->getOrganizerSecret();

        /** @var FormInterface/null $form */
        $form = null;
        if (!$existingRegistration && ($event->isRegistrationPossible() || $organizerSecretValid)) {
            $registration = new Registration();
            if ($user = $this->getUser()) {
                $registration = Registration::createFromUser($event, $user);
            }

            if ($organizerSecretValid) {
                $registration->setIsOrganizer(true);
            }

            $form = $this->createForm(EditType::class, $registration)
                ->add('submit', SubmitType::class, ['translation_domain' => 'index', 'label' => 'register.submit']);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($this->saveRegistration($user, $registration, $request, $emailService, $translator, $guardHandler, $event)) {
                    // update for view
                    $existingRegistration = $registration;
                    $form = null;
                }
            }
        }

        return $this->render('register.html.twig', [
                'existing_registration' => $existingRegistration,
                'event' => $event,
                'form' => $form ? $form->createView() : null,
                'user' => $user,
            ]
        );
    }

    private function saveRegistration(?User $user, Registration $registration, Request $request, EmailServiceInterface $emailService, TranslatorInterface $translator, GuardAuthenticatorHandler $guardHandler, Event $event): bool
    {
        // create user if not exists & login
        if (!$user) {
            $existingUser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $registration->getEmail()]);
            if (null !== $existingUser) {
                $this->notifyUserAlreadyRegistered($request, $existingUser, $emailService, $translator);

                return false;
            }

            $user = $this->createUserAndAuthenticate($registration, $guardHandler, $request);

            $registration->setRelations($event, $user);
        } else {
            $user->updateFromRegistration($registration);
            $this->fastSave($user);
        }

        if ($registration->getIsOrganizer() && !$user->getIsEmailConfirmed()) {
            $this->notifyNeedToConfirmEMail($request, $user, $emailService, $translator);

            return false;
        }

        $registrationRepo = $this->getDoctrine()->getRepository(Registration::class);
        $registrationRepo->save($registration);

        return true;
    }

    private function notifyUserAlreadyRegistered(Request $request, User $user, EmailServiceInterface $emailService, TranslatorInterface $translator): void
    {
        $message = $translator->trans('create.error.already_registered', [], 'security');
        $this->displayError($message);

        // save event url to redirect after login
        $redirectPathKey = '_security.main.target_path';
        $request->getSession()->set($redirectPathKey, $request->getUri());

        $emailService->sendAuthenticateLink($user);
    }

    private function notifyNeedToConfirmEMail(Request $request, User $user, EmailServiceInterface $emailService, TranslatorInterface $translator): void
    {
        $message = $translator->trans('create.error.organizers_need_confirmed_email', [], 'security');
        $this->displayError($message);

        // save event url to redirect after login
        $redirectPathKey = '_security.main.target_path';
        $request->getSession()->set($redirectPathKey, $request->getUri());

        $emailService->sendAuthenticateLink($user);
    }

    private function createUserAndAuthenticate(Registration $registration, GuardAuthenticatorHandler $guardHandler, Request $request): User
    {
        $user = User::createFromRegistration($registration);
        $this->fastSave($user);

        $userToken = new UserToken($user);
        $guardHandler->authenticateWithToken($userToken, $request, 'main');

        return $user;
    }
}
