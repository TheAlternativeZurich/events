<?php

/*
 * This file is part of the TheAlternativeZurich/events project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * Attendee information
 */

trait ContactInformationTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $givenName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $familyName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $streetAddress;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     */
    private $postalCode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     */
    private $locality;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $canton;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Country()
     */
    private $country = 'CH';

    /**
     * @param ContactInformationTrait $other
     */
    protected function fromOtherContactInformation($other)
    {
        $this->givenName = $other->getGivenName();
        $this->familyName = $other->getFamilyName();
        $this->phone = $other->getPhone();
        $this->email = $other->getEmail();
        $this->streetAddress = $other->getStreetAddress();
        $this->postalCode = $other->getPostalCode();
        $this->locality = $other->getLocality();
        $this->canton = $other->getCanton();
        $this->country = $other->getCountry();
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): void
    {
        $this->givenName = $givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): void
    {
        $this->familyName = $familyName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function setStreetAddress(?string $streetAddress): void
    {
        $this->streetAddress = $streetAddress;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(?int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): void
    {
        $this->locality = $locality;
    }

    public function getCanton(): ?string
    {
        return $this->canton;
    }

    public function setCanton(?string $canton): void
    {
        $this->canton = $canton;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * returns all non-empty address lines.
     *
     * @return string[]
     */
    public function getAddressLines()
    {
        $res = explode("\n", $this->getStreetAddress());
        $prefix = '';
        if (mb_strlen($this->getCountry()) > 0) {
            $prefix = $this->getCountry().((mb_strlen($this->getPostalCode()) > 0) ? '-' : ' ');
        }
        if (mb_strlen($this->getPostalCode()) > 0) {
            $prefix .= $this->getPostalCode().' ';
        }
        if (mb_strlen($this->getLocality()) > 0) {
            $prefix .= $this->getLocality().' ';
        }
        if (mb_strlen($this->getCanton()) > 0) {
            $prefix .= $this->getCanton().' ';
        }
        $res[] = trim($prefix);

        $result = [];
        foreach ($res as $entry) {
            if (mb_strlen($entry) > 0) {
                $result[] = $entry;
            }
        }

        return $result;
    }

    public function getName()
    {
        return $this->givenName.' '.$this->familyName;
    }
}
