<?php

namespace Kim1ne\ProductionCalendar;

use \DateTime;

class Period
{
    private ?int $days = null;
    private DateTime $firstDay;
    private DateTime $lastDay;

    public function __construct(
        DateTime $firstDay,
        DateTime $lastDay,
    )
    {
        $this->firstDay = new \DateTime($firstDay->format('d.m.Y'));
        $this->lastDay = new \DateTime($lastDay->format('d.m.Y'));
    }

    public function getFirstDay(): DateTime
    {
        return clone $this->firstDay;
    }

    public function getLastDay(): DateTime
    {
        return clone $this->lastDay;
    }

    public function countDays(): int
    {
        if ($this->days !== null) {
            return $this->days;
        }

        if ($this->firstDay > $this->lastDay) {
            return $this->days = 0;
        }

        $lastDay = clone $this->lastDay;
        $lastDay->modify('+1 day');

        $days = $this->firstDay->diff($lastDay)->days;

        $this->days = $days;

        return $days;
    }
}