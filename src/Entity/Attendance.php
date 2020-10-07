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
class Attendance extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use ContactInformationTrait;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="attendances")
     */
    private $event;

    /**
     * @var Registration
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Registration", inversedBy="attendances")
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
        $attendance = new Attendance();
        $attendance->event = $event;
        $attendance->registration = $registration;
        $attendance->joinDate = new \DateTime();
        $attendance->fromOtherContactInformation($registration);

        return $attendance;
    }

    public function toArray(): array
    {
        $contactInformationArray = $this->toContactInformationArray();
        $contactInformationArray['join'] = $this->joinDate->format('c');
        $contactInformationArray['leave'] = $this->leaveDate ? $this->leaveDate->format('c') : '';

        return $contactInformationArray;
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
