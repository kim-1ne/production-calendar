<?php

namespace Kim1ne\ProductionCalendar\Cache;

use Kim1ne\ProductionCalendar\CalendarCacheInterface;
use Kim1ne\ProductionCalendar\Calendar;

class ProductionCalendarStaticVar implements CalendarCacheInterface
{
    /**
     * @var array<int, Calendar>
     */
    private static array $cache = [];

    public function getByYear(int $year): ?Calendar
    {
        return self::$cache[$year] ?? null;
    }

    public function set(Calendar $productionCalendar): void
    {
        self::$cache[$productionCalendar->getYear()->year] = $productionCalendar;
    }
}