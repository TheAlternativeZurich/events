<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Registration;
use Doctrine\ORM\EntityRepository;

class RegistrationRepository extends EntityRepository
{
    public function save(Registration $registration)
    {
        $maxNumber = 0;
        foreach ($registration->getEvent()->getRegistrations() as $existingRegistration) {
            $maxNumber = max($existingRegistration->getNumber(), $maxNumber);
        }

        ++$maxNumber;

        $registration->setNumber($maxNumber);

        $manager = $this->getEntityManager();
        $manager->persist($registration);
        $manager->flush();
    }

    public function findAllWithAttendance(Event $event)
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.attendances', 'a')
            ->where('r.event = :event_id');

        $qb->setParameter(':event_id', $event->getId());

        return $qb->getQuery()->getResult();
    }
}
