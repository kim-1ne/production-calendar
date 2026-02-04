<?php

namespace Kim1ne\ProductionCalendar;

use Kim1ne\ProductionCalendar\Api\CalendarApi;
use Kim1ne\ProductionCalendar\Api\CalendarApiDataInterface;
use Kim1ne\ProductionCalendar\Cache\ProductionCalendarStaticVar;

class ProductionCalendarFactory
{
    public static function create(
        int $year,
        CalendarCacheInterface $cache = new ProductionCalendarStaticVar(),
        CalendarApiDataInterface $api = new CalendarApi()
    ): Calendar
    {
        $cacheCalendar = $cache->getByYear($year);

        if ($cacheCalendar !== null) {
            return $cacheCalendar;
        }

        $api = $api ?? new CalendarApi();

        $api->setYear($year);

        $calendarApiData = $api->getData();

        $calendar = new Calendar(
            holidays: $calendarApiData->holidays,
            shortDays: $calendarApiData->shortDays
        );

        $cache->set($calendar);

        return $calendar;
    }
}