<?php

namespace Kim1ne\ProductionCalendar\Tests\Integration;

use Kim1ne\ProductionCalendar\Api\CalendarApi;
use Kim1ne\ProductionCalendar\Api\CalendarReferenceData;
use Kim1ne\ProductionCalendar\Cache\ProductionCalendarStaticVar;
use Kim1ne\ProductionCalendar\Calendar;
use PHPUnit\Framework\TestCase;

class CalendarApiIntegrationTest extends TestCase
{
    private CalendarApi $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new CalendarApi();

        $this->api->setYear(2026);
    }

    public function testBuild()
    {
        $reference = $this->api->getData();

        $this->assertInstanceOf(CalendarReferenceData::class, $reference);

        $this->assertNotEmpty($reference->holidays);
        $this->assertNotEmpty($reference->shortDays);

        $calendar = new Calendar($reference->holidays, $reference->shortDays);
        (new ProductionCalendarStaticVar())->set($calendar);
    }
}