<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UserToken extends AbstractToken
{
    public function __construct(User $user)
    {
        parent::__construct($user->getRoles());

        $this->setUser($user->getEmail());
        $this->setAuthenticated(true);
    }

    /**
     * This is meant to be only an authenticated token, where credentials
     * have already been used and are thus cleared.
     *
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return [];
    }
}
