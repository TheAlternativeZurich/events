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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventVoter
{
    const EVENT_ADD_SELF = 'event_add_self';
    const EVENT_VIEW = 'event_view';
    const EVENT_MODIFY = 'event_modify';

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
        if (!in_array($attribute, [self::EVENT_ADD_SELF, self::EVENT_VIEW, self::EVENT_MODIFY])) {
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
        $user = $token->getUser();

        if ($user instanceof User) {
            switch ($attribute) {
                case self::EVENT_VIEW:
                case self::EVENT_MODIFY:
                    return $subject->get()->contains($user);
                case self::EVENT_ADD_SELF:
                    return !$user->getIsTrialAccount() && !$user->getIsExternalAccount();
            }
        }

        throw new \LogicException('Attribute '.$attribute.' unknown!');
    }
}
