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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Participation extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use ContactInformationTrait;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="participations")
     */
    private $event;

    /**
     * @var Registration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Registration", inversedBy="participations")
     */
    private $registration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $joinDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $leaveDate;

    public static function create(Event $event, Registration $registration)
    {
        $participation = new Participation();
        $participation->event = $event;
        $participation->registration = $registration;
        $participation->joinDate = new \DateTime();
        $participation->fromOtherContactInformation($registration);

        return $participation;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getRegistration(): Registration
    {
        return $this->registration;
    }

    public function getJoinDate(): \DateTime
    {
        return $this->joinDate;
    }

    public function getLeaveDate(): ?\DateTime
    {
        return $this->leaveDate;
    }

    public function setLeaveDate(?\DateTime $leaveDate): void
    {
        $this->leaveDate = $leaveDate;
    }
}
