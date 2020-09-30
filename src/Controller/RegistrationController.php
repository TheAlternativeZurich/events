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
use App\Entity\Registration;
use App\Form\Registration\EditType;
use App\Security\Voter\RegistrationVoter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/registration")
 */
class RegistrationController extends BaseDoctrineController
{
    /**
     * @Route("/{registration}/update", name="registration_update")
     *
     * @return Response
     */
    public function updateAction(Request $request, Registration $registration, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(RegistrationVoter::REGISTRATION_UPDATE, $registration);

        $form = $this->createForm(EditType::class, $registration)
            ->add('submit', SubmitType::class, ['translation_domain' => 'registration', 'label' => 'update.submit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->fastSave($registration);

            $message = $translator->trans('update.success.updated', [], 'registration');
            $this->displaySuccess($message);

            return $this->redirectToRoute('register', ['identifier' => $registration->getEvent()->getIdentifier()]);
        }

        return $this->render('registration/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{registration}/delete", name="registration_delete")
     *
     * @return Response
     */
    public function deleteAction(Request $request, Registration $registration, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(RegistrationVoter::REGISTRATION_DELETE, $registration);

        if ($request->query->has('confirm')) {
            if (!$registration->getIsOrganizer() || count($registration->getEvent()->getOrganizerRegistrations()) > 1) {
                $this->fastRemove($registration);

                $message = $translator->trans('delete.success.deleted', [], 'registration');
                $this->displaySuccess($message);

                return $this->redirectToRoute('register', ['identifier' => $registration->getEvent()->getIdentifier()]);
            } else {
                $message = $translator->trans('delete.error.last_organizer', [], 'registration');
                $this->displayError($message);

                return $this->redirectToRoute('event_view', ['event' => $registration->getEvent()->getId()]);
            }
        }

        return $this->render('registration/delete.html.twig', ['registration' => $registration]);
    }
}
