<?php

namespace Kim1ne\ProductionCalendar;

readonly class Month
{
    public function __construct(
        public int   $year,
        public int   $month,
        public int   $workingDays,
        public int   $hours,
        public array $shortDays = [],
        public array $holidays = [],
    ) {}
}