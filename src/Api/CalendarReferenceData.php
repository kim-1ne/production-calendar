<?php

namespace Kim1ne\ProductionCalendar\Api;

class CalendarReferenceData
{
    public function __construct(
        public readonly array $shortDays = [],
        public readonly array $holidays = [],
    ) {}
}