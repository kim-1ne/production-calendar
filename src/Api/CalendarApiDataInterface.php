<?php

namespace Kim1ne\ProductionCalendar\Api;

interface CalendarApiDataInterface
{
    public function getData(): CalendarReferenceData;

    public function setYear(int $year): void;
}