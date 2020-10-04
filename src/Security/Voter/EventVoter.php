<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EventVoter extends Voter
{
    const EVENT_VIEW = 'event_view';
    const EVENT_UPDATE = 'event_update';
    const EVENT_CREATE = 'êvent_create';

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * EventVoter constructor.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param Event  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EVENT_CREATE, self::EVENT_VIEW, self::EVENT_UPDATE])) {
            return false;
        }

        return $subject instanceof Event;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Event  $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $email = $token->getUser();
        /** @var User|null $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null === $user) {
            return false;
        }

        if (self::EVENT_CREATE === $attribute) {
            return $user->getIsEmailConfirmed();
        }

        $matchingRegistration = $user->getRegistrationFor($subject);
        if (null === $matchingRegistration) {
            return false;
        }

        switch ($attribute) {
            case self::EVENT_VIEW:
            case self::EVENT_UPDATE:
                return $matchingRegistration->getIsOrganizer();
        }

        throw new \LogicException('Attribute '.$attribute.' unknown!');
    }
}
