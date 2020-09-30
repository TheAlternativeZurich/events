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
use App\Helper\IdentifierHelper;
use App\Helper\RandomHelper;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function save(Event $event)
    {
        $identifierContent = $event->getOrganizer().'-'.$event->getName();
        $optimalIdentifier = IdentifierHelper::getHumanReadableIdentifier($identifierContent);
        $identifier = $optimalIdentifier;

        $number = 1;
        while ($this->findOneBy(['identifier' => $identifier])) {
            $identifier = $optimalIdentifier.$number++;
        }

        $random = RandomHelper::generateHumanReadableRandom(9, '-');

        $event->setIdentifiers($identifier, $random);

        $manager = $this->getEntityManager();
        $manager->persist($event);
        $manager->flush();
    }
}
