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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

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
     * @Route("r/{identifier}", name="register", priority="-10")
     *
     * @return Response
     */
    public function registerAction(string $identifier, Request $request, GuardAuthenticatorHandler $guardHandler)
    {
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

        /** @var FormInterface/null $form */
        $form = null;
        if (!$existingRegistration && $event->isRegistrationPossible()) {
            $registration = new Registration();
            if ($user = $this->getUser()) {
                $registration = Registration::createFromUser($event, $user);
            }

            if ($request->query->has('organizer-secret') && $request->query->get('organizer-secret') === $event->getOrganizerSecret()) {
                $registration->setIsOrganizer(true);
            }

            $form = $this->createForm(EditType::class, $registration)
                ->add('submit', SubmitType::class, ['translation_domain' => 'index', 'label' => 'register.submit']);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $registrationRepo = $this->getDoctrine()->getRepository(Registration::class);
                $registrationRepo->save($registration);

                // update for view
                $existingRegistration = $registration;
                $form = null;

                // create user if not exists & login
                if (!$user) {
                    $user = User::createFromRegistration($registration);

                    $userToken = new UserToken($user);
                    $guardHandler->authenticateWithToken($userToken, $request, 'main');
                }
            }
        }

        return $this->render('register.html.twig', ['existing_registration' => $existingRegistration, 'event' => $event, 'form' => $form ? $form->createView() : null]);
    }
}
