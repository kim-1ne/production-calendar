<?php

namespace Kim1ne\ProductionCalendar;

interface CalendarCacheInterface
{
    public function getByYear(int $year): ?Calendar;

    public function set(Calendar $productionCalendar): void;
}