<?php

namespace Kim1ne\ProductionCalendar\Tests\Unit;

use Kim1ne\ProductionCalendar\Calendar;
use Kim1ne\ProductionCalendar\Month;
use PHPUnit\Framework\TestCase;
use DateTime;

class MonthTest extends TestCase
{
    private Month $month;

    protected function setUp(): void
    {
        parent::setUp();

        $holidays2024 = [
            new DateTime('2024-01-01'),
            new DateTime('2024-01-02'),
        ];

        $shortDays2024 = [
            new DateTime('2024-01-22'),
        ];

        $calendar = new Calendar(
            holidays: $holidays2024,
            workingSaturdays: [],
            shortDays: $shortDays2024
        );

        $year = $calendar->getYear();

        $this->month = $year->getMonth(1);
    }

    public function testJanuary()
    {
        $this->assertEquals(1, $this->month->month);
    }

    public function testDaysInMonth(): void
    {
        $this->assertCount(1, $this->month->shortDays);
        $this->assertCount(2, $this->month->holidays);
    }

    public function testYear(): void
    {
        $this->assertEquals('2024', $this->month->year);
    }

    public function testCountHours(): void
    {
        $countShortDays = count($this->month->shortDays);

        $workingDays = $this->month->workingDays;

        $this->assertEquals($workingDays * 8 - $countShortDays, $this->month->hours);
    }
}