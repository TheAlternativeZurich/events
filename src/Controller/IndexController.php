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

use App\Controller\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class IndexController extends BaseController
{
    /**
     * @Route("", name="index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $registrations = $this->getUser()->getRegistrations();

        $upcomingRegistrations = [];
        $pastRegistrations = [];
        foreach ($registrations as $registration) {
            $event = $registration->getEvent();

            $key = $event->getStartDate()->format('c').'_'.$event->getId();
            if (null !== $event->getClosedDate()) {
                $upcomingRegistrations[$key] = $event;
            } else {
                $pastRegistrations[$key] = $event;
            }
        }

        krsort($upcomingRegistrations);
        krsort($pastRegistrations);

        return $this->render('index.html.twig', ['upcoming_registrations' => $upcomingRegistrations, 'past_registrations' => $pastRegistrations]);
    }
}
