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
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RegistrationVoter extends Voter
{
    const REGISTRATION_UPDATE = 'registration_update';
    const REGISTRATION_DELETE = 'registration_delete';

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
        if (!in_array($attribute, [self::REGISTRATION_UPDATE, self::REGISTRATION_DELETE])) {
            return false;
        }

        return $subject instanceof Registration;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string       $attribute
     * @param Registration $subject
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

        switch ($attribute) {
            case self::REGISTRATION_UPDATE:
            case self::REGISTRATION_DELETE:
                return $subject->getUser() === $user;
        }

        throw new \LogicException('Attribute '.$attribute.' unknown!');
    }
}
