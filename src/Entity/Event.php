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
use App\Entity\Traits\EventTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Event extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use EventTrait;

    /**
     * when the registration starts being accessible.
     *
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationOpen;

    /**
     * when the registration is no longer possible.
     *
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $registrationClose;

    /**
     * when the event was finished, hence all open participations are closed.
     *
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedDate;

    /**
     * @var Registration[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Registration", mappedBy="event")
     */
    private $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function getRegistrationOpen(): ?\DateTime
    {
        return $this->registrationOpen;
    }

    public function setRegistrationOpen(?\DateTime $registrationOpen): void
    {
        $this->registrationOpen = $registrationOpen;
    }

    public function getRegistrationClose(): ?\DateTime
    {
        return $this->registrationClose;
    }

    public function setRegistrationClose(?\DateTime $registrationClose): void
    {
        $this->registrationClose = $registrationClose;
    }

    public function getClosedDate(): ?\DateTime
    {
        return $this->closedDate;
    }

    public function close()
    {
        $this->closedDate = new \DateTime();
    }

    /**
     * @return Registration[]|ArrayCollection
     */
    public function getRegistrations()
    {
        return $this->registrations;
    }
}
