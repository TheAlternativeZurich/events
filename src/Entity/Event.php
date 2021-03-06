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
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Event extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use EventTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $organizerSecret;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maximumAttendeeCapacity;

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
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $registrations;

    /**
     * @var Attendance[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Attendance", mappedBy="event")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $attendances;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->attendances = new ArrayCollection();
    }

    public function setIdentifiers(string $identifier, string $organizerSecret)
    {
        $this->identifier = $identifier;
        $this->organizerSecret = $organizerSecret;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getOrganizerSecret(): string
    {
        return $this->organizerSecret;
    }

    public function getMaximumAttendeeCapacity(): ?int
    {
        return $this->maximumAttendeeCapacity;
    }

    public function setMaximumAttendeeCapacity(?int $maximumAttendeeCapacity): void
    {
        $this->maximumAttendeeCapacity = $maximumAttendeeCapacity;
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

    public function isRegistrationOpen(): bool
    {
        if ($this->closedDate) {
            return false;
        }

        if ($this->isBeforeRegistrationPeriod()) {
            return false;
        }
        if ($this->isAfterRegistrationPeriod()) {
            return false;
        }

        return true;
    }

    public function isBeforeRegistrationPeriod()
    {
        $now = new \DateTime();

        return null !== $this->registrationOpen && $this->registrationOpen > $now;
    }

    public function isAfterRegistrationPeriod()
    {
        $now = new \DateTime();

        return null !== $this->registrationClose && $this->registrationClose < $now;
    }

    public function isRegistrationPossible(): bool
    {
        $placesLeft = $this->placesLeft();

        return (null === $placesLeft || $placesLeft > 0) && $this->isRegistrationOpen();
    }

    public function placesLeft(): ?int
    {
        if (null === $this->maximumAttendeeCapacity) {
            return null;
        }

        $participantRegistrationCount = 0;
        foreach ($this->registrations as $registration) {
            if (!$registration->getIsOrganizer()) {
                ++$participantRegistrationCount;
            }
        }

        return max($this->maximumAttendeeCapacity - $participantRegistrationCount, 0);
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

    /**
     * @return Registration[]
     */
    public function getOrganizerRegistrations(): array
    {
        $organizerRegistrations = [];

        foreach ($this->registrations as $registration) {
            if ($registration->getIsOrganizer()) {
                $organizerRegistrations[] = $registration;
            }
        }

        return $organizerRegistrations;
    }

    /**
     * @return Registration[]
     */
    public function getParticipantRegistrations(): array
    {
        $participantRegistrations = [];

        foreach ($this->registrations as $registration) {
            if (!$registration->getIsOrganizer()) {
                $participantRegistrations[] = $registration;
            }
        }

        return $participantRegistrations;
    }

    /**
     * @return Attendance[]|ArrayCollection
     */
    public function getAttendances()
    {
        return $this->attendances;
    }
}
