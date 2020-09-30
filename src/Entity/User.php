<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\ContactInformationTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Helper\HashHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use ContactInformationTrait;

    // can use any features & impersonate users
    const ROLE_ADMIN = 'ROLE_ADMIN';

    // can use any features
    const ROLE_USER = 'ROLE_USER';

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $authenticationHash;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdminAccount = false;

    /**
     * @var Registration[]|ArrayCollection
     *
     * @ORM\OneToMany (targetEntity="App\Entity\Registration", mappedBy="user")
     */
    private $registrations;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public static function createFromRegistration(Registration $registration)
    {
        $user = new User();
        $user->fromOtherContactInformation($registration);

        return $user;
    }

    /**
     * @return Registration[]|ArrayCollection
     */
    public function getRegistrations()
    {
        return $this->registrations;
    }

    public function generateAuthenticationHash()
    {
        $this->authenticationHash = HashHelper::getHash();
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles()
    {
        if ($this->isAdminAccount) {
            return [self::ROLE_ADMIN];
        }

        return [self::ROLE_USER];
    }

    public function getAuthenticationHash(): ?string
    {
        return $this->authenticationHash;
    }

    public function getRegistrationFor(Event $event): ?Registration
    {
        foreach ($this->getRegistrations() as $registration) {
            if ($registration->getEvent() === $event) {
                return $registration;
            }
        }

        return null;
    }
}
