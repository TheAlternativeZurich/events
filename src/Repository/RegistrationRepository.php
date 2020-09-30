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

use App\Entity\Registration;
use Doctrine\ORM\EntityRepository;

class RegistrationRepository extends EntityRepository
{
    public function save(Registration $registration)
    {
        $maxNumber = 0;
        foreach ($registration->getEvent()->getRegistrations() as $registration) {
            $maxNumber = max($registration->getNumber(), $maxNumber);
        }

        ++$maxNumber;

        $registration->setNumber($maxNumber);

        $manager = $this->getEntityManager();
        $manager->persist($registration);
        $manager->flush();
    }
}
