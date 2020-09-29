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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Registration extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use ContactInformationTrait;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isOrganizer = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="registrations")
     */
    private $event;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="registrations")
     */
    private $user;

    /**
     * @var Participation[]|ArrayCollection
     *
     * @ORM\OneToMany (targetEntity="App\Entity\Participation", mappedBy="registration")
     */
    private $participations;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
    }

    public static function createFromUser(Event $event, User $user, int $registrationNumber, bool $isOrganizer = false)
    {
        $registration = new Registration();
        $registration->event = $event;
        $registration->user = $user;
        $registration->number = $registrationNumber;
        $registration->isOrganizer = $isOrganizer;
        $registration->fromOtherContactInformation($user);

        return $registration;
    }

    public function getIsOrganizer(): bool
    {
        return $this->isOrganizer;
    }

    public function setIsOrganizer(bool $isOrganizer): void
    {
        $this->isOrganizer = $isOrganizer;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
