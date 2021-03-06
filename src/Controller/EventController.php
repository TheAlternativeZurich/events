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
use App\Entity\Attendance;
use App\Entity\Event;
use App\Entity\Registration;
use App\Form\Event\EditType;
use App\Security\Voter\EventVoter;
use App\Service\Interfaces\CsvServiceInterface;
use App\Service\Interfaces\EmailServiceInterface;
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
    public function createAction(Request $request, TranslatorInterface $translator, EmailServiceInterface $emailService)
    {
        if (!$this->getUser()->getIsEmailConfirmed()) {
            $message = $translator->trans('create.error.email_not_yet_confirmed', [], 'event');
            $this->displayDanger($message);

            $emailService->sendAuthenticateLink(/* @scrutinizer ignore-type */ $this->getUser());

            return $this->redirectToRoute('index');
        }

        $this->denyAccessUnlessGranted(EventVoter::EVENT_CREATE, new Event());

        $event = new Event();
        $form = $this->createForm(EditType::class, $event)
            ->add('submit', SubmitType::class, ['translation_domain' => 'event', 'label' => 'create.submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository = $this->getDoctrine()->getRepository(Event::class);
            $eventRepository->save($event);

            $registration = Registration::createFromUser($event, /* @scrutinizer ignore-type */ $this->getUser(), true);
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

        $ownRegistration = $this->getUser()->getRegistrationFor($event);
        $participantRegistrations = $event->getParticipantRegistrations();
        $organizerRegistrations = $event->getOrganizerRegistrations();

        return $this->render('event/view.html.twig', ['event' => $event, 'participant_registrations' => $participantRegistrations, 'organizer_registrations' => $organizerRegistrations, 'own_registration' => $ownRegistration]);
    }

    /**
     * @Route("/{event}/attendance", name="event_attendance")
     *
     * @return Response
     */
    public function attendanceAction(Event $event)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_VIEW, $event);

        $registrations = $this->getDoctrine()->getRepository(Registration::class)->findAllWithAttendance($event);

        return $this->render('event/attendance.html.twig', ['event' => $event, 'registrations' => $registrations]);
    }

    /**
     * @Route("/{event}/{registration}/join", name="event_join")
     *
     * @return Response
     */
    public function joinAction(Event $event, Registration $registration, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_VIEW, $event);
        if ($registration->getEvent() !== $event) {
            throw new NotFoundHttpException();
        }

        if (null !== $registration->getActiveAttendance()) {
            $message = $translator->trans('join.error.already_attending', [], 'event');
            $this->displayError($message);
        } else {
            $attendance = Attendance::create($event, $registration);
            $this->fastSave($attendance);
        }

        return $this->redirectToRoute('event_attendance', ['event' => $event->getId()]);
    }

    /**
     * @Route("/{event}/{attendance}/leave", name="event_leave")
     *
     * @return Response
     */
    public function leaveAction(Event $event, Attendance $attendance, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_VIEW, $event);
        if ($attendance->getEvent() !== $event) {
            throw new NotFoundHttpException();
        }

        if ($attendance->getLeaveDate()) {
            $message = $translator->trans('leave.error.already_left', [], 'event');
            $this->displayError($message);
        } else {
            $attendance->setLeaveDate(new \DateTime());
            $this->fastSave($attendance);
        }

        return $this->redirectToRoute('event_attendance', ['event' => $event->getId()]);
    }

    /**
     * @Route("/{event}/attendance/export", name="event_attendance_export")
     *
     * @return Response
     */
    public function attendanceExportAction(Event $event, TranslatorInterface $translator, CsvServiceInterface $csvService)
    {
        $this->denyAccessUnlessGranted(EventVoter::EVENT_VIEW, $event);

        if (0 === $event->getAttendances()->count()) {
            $message = $translator->trans('attendance.error.no_attendance_yet', [], 'event');
            $this->displayError($message);

            return $this->redirectToRoute('event_view', ['event' => $event->getId()]);
        }

        $now = new \DateTime();
        $eventPrefix = $event->getOrganizer().' - '.$event->getName();
        $filename = $eventPrefix.'- '.$now->format('c').'.csv';

        $header = array_keys($event->getAttendances()[0]->toArray());
        $values = [];
        foreach ($event->getAttendances() as $attendance) {
            $values[] = array_values($attendance->toArray());
        }

        return $csvService->streamCsv($filename, $values, $header);
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

        // owners have to deregister themselves
        if ($registration->getIsOrganizer()) {
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
