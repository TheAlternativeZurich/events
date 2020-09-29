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
use App\Form\Event\EditType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        if ($this->tryProcessForm($event, $request, $form)) {
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
        return $this->render('event/view.html.twig', ['event' => $event]);
    }

    /**
     * @Route("/{event}/update", name="event_update")
     *
     * @return Response
     */
    public function updateAction(Request $request, Event $event, TranslatorInterface $translator)
    {
        if ($this->tryProcessForm($event, $request, $form)) {
            $message = $translator->trans('update.success.updated', [], 'event');
            $this->displaySuccess($message);

            return $this->redirectToRoute('event_view', ['event' => $event->getId()]);
        }

        return $this->render('event/update.html.twig', ['form' => $form->createView()]);
    }

    private function tryProcessForm(Event $event, Request $request, ?FormInterface &$form): bool
    {
        $form = $this->createForm(EditType::class, $event)
            ->add('submit', SubmitType::class, ['translation_domain' => 'event', 'label' => 'create.submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->fastSave($event);

            return true;
        }

        return false;
    }
}
