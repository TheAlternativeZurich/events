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
use App\Entity\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 */
class EventController extends BaseController
{
    /**
     * @Route("/{event}", name="event")
     *
     * @return Response
     */
    public function eventAction(Event $event)
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/create", name="event_create")
     *
     * @return Response
     */
    public function newAction()
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/{event}/update", name="event_update")
     *
     * @return Response
     */
    public function editAction(Event $event)
    {
        return $this->render('index.html.twig');
    }
}
