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

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements OrderedFixtureInterface
{
    const ORDER = 0;
    const TESTER_REFERENCE = User::class.'_tester';

    public function load(ObjectManager $manager)
    {
        $entries = [
            ['f@thealternative.ch', 'asdf', 'Florian', 'Moser', '0781234567', 'Jäherweg 2', '8003', 'Zürich', 'Zürich', 'CH'],
        ];

        foreach ($entries as $entry) {
            $user = new User();
            $user->setEmail($entry[0]);
            $user->setGivenName($entry[2]);
            $user->setFamilyName($entry[3]);
            $user->setPhone($entry[4]);

            $user->setStreetAddress($entry[5]);
            $user->setPostalCode($entry[6]);
            $user->setLocality($entry[7]);
            $user->setCanton($entry[8]);
            $user->setCountry($entry[9]);
            $manager->persist($user);

            $this->addReference(self::TESTER_REFERENCE, $user);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }
}
