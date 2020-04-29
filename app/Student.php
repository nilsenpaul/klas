<?php
declare(strict_types=1);

namespace bslagter\klas\app;

final class Student
{
    public const REASON_FROM_ADDRESS = 'Adres';
    public const REASON_FROM_ZIPCODE = 'Postcode';
    public const REASON_FROM_PREFERENCE = 'Voorkeur';
    public const REASON_RANDOM = 'Willekeurig';

    /** @var Group */
    private $group;
    /** @var string */
    private $name;
    /** @var Address */
    private $address;
    /** @var Zipcode */
    private $zipcode;
    /** @var int */
    private $preference;
    /** @var int */
    private $segment;
    /** @var string */
    private $segmentReason;

    public function __construct(Group $group, string $name, Address $address, Zipcode $zipcode, Int $preference)
    {
        $this->group = $group;
        $this->name = $name;
        $this->address = $address;
        $this->zipcode = $zipcode;
        $this->preference = $preference;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getZipcode(): Zipcode
    {
        return $this->zipcode;
    }

    public function getSegment(): int
    {
        return $this->segment;
    }

    public function getPreference(): int
    {
        return $this->preference;
    }

    public function getSegmentPlusOne(): int
    {
        return $this->segment + 1;
    }

    public function setSegment(int $segment, string $reason): self
    {
        $this->segment = $segment;
        $this->segmentReason = $reason;
        return $this;
    }

    public function hasSegment(): bool
    {
        return isset($this->segment);
    }

    public function getSegmentReason(): string
    {
        return $this->segmentReason;
    }
}
