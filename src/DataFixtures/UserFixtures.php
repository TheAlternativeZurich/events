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

    public function load(ObjectManager $manager)
    {
        $entries = [
            ['f@thealternative.ch', 'asdf', 'Florian', 'Moser'],
        ];

        foreach ($entries as $entry) {
            $user = new User();
            $user->setEmail($entry[0]);
            $user->setGivenName($entry[2]);
            $user->setFamilyName($entry[3]);
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }
}
