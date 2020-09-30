<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements OrderedFixtureInterface
{
    const ORDER = UserFixtures::ORDER + 1;

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::TESTER_REFERENCE);

        $entries = [
            [
                ['TheAlternative', 'Console Toolkit 2020', 'Learn how to master the console!', '2020-12-22T18:00:00', null], // event details
                [null, null, null, false], // registration restrictions: no restrictions
            ],
            [
                ['TheAlternative', 'Free Software & Open Source 2020', 'The basics about Free Software & Open Source and why you should use it.', '2020-12-26T18:00:00', '2020-12-26T20:00:00'], // event details
                [10, null, null, false], // registration restrictions: max 10 participants
            ],
            [
                ['TheAlternative', 'Bash Workshops 2020', 'Bash commands & Linux Basics to master your studies.', '2020-12-26T18:00:00', null], // event details
                [10, '2019-12-26T18:00:00', '2022-12-26T18:00:00', false], // registration restrictions: open
            ],
            [
                ['TheAlternative', 'Bash Workshops 2019', 'Bash commands & Linux Basics to master your studies.', '2020-12-26T18:00:00', null], // event details
                [10, '2019-12-26T18:00:00', '2019-12-26T20:00:00', false], // registration restrictions: closed
            ],
            [
                ['TheAlternative', 'Console Toolkit 2019', 'Learn how to master the console!', '2020-12-22T18:00:00', null], // event details
                [null, null, null, true], // registration restrictions: closed
            ],
            [
                ['TheAlternative', 'Free Software & Open Source 2019', 'The basics about Free Software & Open Source and why you should use it.', '2020-12-26T18:00:00', '2020-12-26T20:00:00'], // event details
                [null, null, null, true], // registration restrictions: max 10 participants
            ],
        ];

        foreach ($entries as $entry) {
            $event = new Event();

            $eventDetails = $entry[0];
            $event->setOrganizer($eventDetails[0]);
            $event->setName($eventDetails[1]);
            $event->setDescription($eventDetails[2]);
            $event->setStartDate(new \DateTime($eventDetails[3]));
            if (null !== $eventDetails[4]) {
                $event->setEndDate(new \DateTime($eventDetails[4]));
            }

            $registrationRestrictions = $entry[1];
            if (null !== $registrationRestrictions[0]) {
                $event->setMaximumAttendeeCapacity($registrationRestrictions[0]);
            }
            if (null !== $registrationRestrictions[1]) {
                $event->setRegistrationOpen(new \DateTime($registrationRestrictions[1]));
            }
            if (null !== $registrationRestrictions[2]) {
                $event->setRegistrationClose(new \DateTime($registrationRestrictions[2]));
            }
            if ($registrationRestrictions[3]) {
                $event->close();
            }

            $manager->getRepository(Event::class)->save($event);

            $registration = Registration::createFromUser($event, $user, true);
            $registrationRepository = $manager->getRepository(Registration::class);
            $registrationRepository->save($registration);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }
}
