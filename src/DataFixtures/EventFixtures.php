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
            ['TheAlternative', 'Console Toolkit', 'Learn how to master the console!', '2020-12-22T18:00:00', null, null, null],
            ['TheAlternative', 'Free Software & Open Source', 'The basics about Free Software & Open Source and why you should use it.', '2020-12-26T18:00:00', 10, null, null],
            ['TheAlternative', 'Bash Workshop', 'Bash commands & Linux Basics to master your studies.', '2020-01-27T18:00:00', 3, '2020-01-27T18:00:00', '2020-01-28T18:00:00'],
        ];

        foreach ($entries as $entry) {
            $event = new Event();
            $event->setOrganizer($entry[0]);
            $event->setName($entry[1]);
            $event->setDescription($entry[2]);
            $event->setStartDate(new \DateTime($entry[3]));
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
