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
use App\Form\Event\EditType;
use App\Security\Voter\EventVoter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/event")
 */
class EventController extends BaseDoctrineController
{
    /**
     * @Route("/create", name="event_create")
     *
     * @return Response
     */
    public function createAction(Request $request, TranslatorInterface $translator)
    {
        $event = new Event();
        $form = $this->createForm(EditType::class, $event)
            ->add('submit', SubmitType::class, ['translation_domain' => 'event', 'label' => 'create.submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository = $this->getDoctrine()->getRepository(Event::class);
            $eventRepository->save($event);

            $registration = Registration::createFromUser($event, $this->getUser(), true);
            $registrationRepository = $this->getDoctrine()->getRepository(Registration::class);
            $registrationRepository->save($registration);

            $message = $translator->trans('create.success.created', [], 'event');
            $this->displaySuccess($message);

            return $this->redirectToRoute('event_view', ['event' => $event->getId()]);
        }

        return $this->render('event/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{event}", name="event_view")
     *
     * @return Response
     */
    public function viewAction(Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_VIEW, $event);

        $registrations = $event->getRegistrations();

        /** @var Registration[] $participantRegistrations */
        $participantRegistrations = [];
        /** @var Registration[] $organizerRegistrations */
        $organizerRegistrations = [];
        foreach ($registrations as $registration) {
            $key = $registration->getCreatedAt()->format('c').'_'.$registration->getId();

            if ($registration->getIsOrganizer()) {
                $organizerRegistrations[$key] = $registration;
            } else {
                $participantRegistrations[$key] = $registration;
            }
        }

        return $this->render('event/view.html.twig', ['event' => $event, 'participant_registrations' => $participantRegistrations, 'organizerRegistrations' => $organizerRegistrations]);
    }

    /**
     * @Route("/{event}/deregister/{registration}", name="event_deregister")
     *
     * @return Response
     */
    public function deregisterAction(Request $request, Event $event, Registration $registration, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_VIEW, $event);

        if ($registration->getEvent() !== $event) {
            throw new NotFoundHttpException();
        }

        if ($request->query->has('confirm')) {
            $this->fastRemove($registration);

            $message = $translator->trans('deregister.success.deregistered', [], 'event');
            $this->displaySuccess($message);

            return $this->redirectToRoute('event_view', ['id' => $event->getId()]);
        }

        return $this->render('event/deregister.html.twig', ['registration' => $registration]);
    }

    /**
     * @Route("/{event}/update", name="event_update")
     *
     * @return Response
     */
    public function updateAction(Request $request, Event $event, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_UPDATE, $event);

        $form = $this->createForm(EditType::class, $event)
            ->add('submit', SubmitType::class, ['translation_domain' => 'event', 'label' => 'update.submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->fastSave($event);

            $message = $translator->trans('update.success.updated', [], 'event');
            $this->displaySuccess($message);

            return $this->redirectToRoute('event_view', ['event' => $event->getId()]);
        }

        return $this->render('event/update.html.twig', ['form' => $form->createView()]);
    }
}
