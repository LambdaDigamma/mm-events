<?php

namespace LambdaDigamma\MMEvents;

use DateTimeInterface;
use LambdaDigamma\MMEvents\Exceptions\InvalidLink;
use LambdaDigamma\MMEvents\Generators\Ics;

/**
 * @property-read string $title
 * @property-read DateTimeInterface|\DateTime|\DateTimeImmutable $from
 * @property-read DateTimeInterface|\DateTime|\DateTimeImmutable $to
 * @property-read string $description
 * @property-read string $address
 * @property-read bool $allDay
 */
class Link
{
    /** @var string */
    protected $title;

    /** @var \DateTime */
    protected $from;

    /** @var \DateTime */
    protected $to;

    /** @var string */
    protected $description;

    /** @var bool */
    protected $allDay;

    /** @var string */
    protected $address;

    public function __construct(string $title, DateTimeInterface $from, DateTimeInterface $to, bool $allDay = false)
    {
        $this->title = $title;
        $this->allDay = $allDay;

        if ($to < $from) {
            throw InvalidLink::invalidDateRange($from, $to);
        }

        $this->from = clone $from;
        $this->to = clone $to;
    }

    /**
     * @return static
     *
     * @throws InvalidLink
     */
    public static function create(string $title, DateTimeInterface $from, DateTimeInterface $to, bool $allDay = false)
    {
        return new static($title, $from, $to, $allDay);
    }

    /**
     * @param  DateTimeInterface|\DateTime|\DateTimeImmutable  $fromDate
     *
     * @throws InvalidLink
     */
    public static function createAllDay(string $title, DateTimeInterface $fromDate, int $numberOfDays = 1): self
    {
        $from = (clone $fromDate)->modify('midnight');
        $to = (clone $from)->modify("+$numberOfDays days");

        return new self($title, $from, $to, true);
    }

    /**
     * @return $this
     */
    public function description(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return $this
     */
    public function address(string $address)
    {
        $this->address = $address;

        return $this;
    }

    public function formatWith(Generator $generator): string
    {
        return $generator->generate($this);
    }

    public function ics(array $options = []): string
    {
        return $this->formatWith(new Ics($options));
    }

    public function __get($property)
    {
        return $this->$property;
    }
}
