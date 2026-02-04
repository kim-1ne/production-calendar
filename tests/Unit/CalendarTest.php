<?php

namespace Kim1ne\ProductionCalendar\Tests\Unit;

use Kim1ne\ProductionCalendar\Calendar;
use PHPUnit\Framework\TestCase;
use DateTime;

class CalendarTest extends TestCase
{
    private Calendar $calendar2024;

    protected function setUp(): void
    {
        parent::setUp();

        $holidays2024 = [
            new DateTime('2024-01-01'),
            new DateTime('2024-01-02'),
            new DateTime('2024-01-03'),
            new DateTime('2024-01-04'),
            new DateTime('2024-01-05'),
            new DateTime('2024-01-06'),
            new DateTime('2024-01-07'),
            new DateTime('2024-01-08'),
            new DateTime('2024-02-23'),
            new DateTime('2024-03-08'),
            new DateTime('2024-05-01'),
            new DateTime('2024-05-09'),
            new DateTime('2024-06-12'),
            new DateTime('2024-11-04'),
        ];

        $shortDays2024 = [
            new DateTime('2024-02-22'),
            new DateTime('2024-03-07'),
            new DateTime('2024-05-08'),
            new DateTime('2024-11-01'),
        ];

        $this->calendar2024 = new Calendar(
            holidays: $holidays2024,
            workingSaturdays: [],
            shortDays: $shortDays2024
        );
    }

    public function testCalendarCreation(): void
    {
        $this->assertInstanceOf(Calendar::class, $this->calendar2024);

        $year = $this->calendar2024->getYear();
        $this->assertEquals(2024, $year->year);
    }

    public function testIsShortDay(): void
    {
        $shortDay = new DateTime('2024-02-22');
        $this->assertTrue($this->calendar2024->isShortDay($shortDay));

        $regularDay = new DateTime('2024-02-21');
        $this->assertFalse($this->calendar2024->isShortDay($regularDay));
    }

    public function testIsWorkDay(): void
    {
        $workDay = new DateTime('2024-02-21'); // Среда
        $this->assertTrue($this->calendar2024->isWorkDay($workDay));

        $sunday = new DateTime('2024-02-25');
        $this->assertFalse($this->calendar2024->isWorkDay($sunday));

        $holiday = new DateTime('2024-01-01');
        $this->assertFalse($this->calendar2024->isWorkDay($holiday));
    }

    public function testPeriodCalculation(): void
    {
        $from = new DateTime('2024-01-01');
        $to = new DateTime('2024-01-31');

        $period = $this->calendar2024->period($from, $to);

        $this->assertEquals(2024, $period->year);

        $january = $period->getMonth(1);
        $this->assertNotNull($january);
        $this->assertEquals(1, $january->month);

        $periods = $period->getPeriods();
        $this->assertCount(1, $periods);
    }

    public function testGetYearReturnsYearObject(): void
    {
        $year = $this->calendar2024->getYear();

        $this->assertEquals(2024, $year->year);
        $this->assertGreaterThan(0, $year->getWorkingDays());
        $this->assertGreaterThan(0, $year->getHours());

        $months = $year->getMonths();
        $this->assertCount(12, $months);
    }

    public function testPeriodDatesWithMultiplePeriods(): void
    {
        $period1From = new DateTime('2024-01-01');
        $period1To = new DateTime('2024-01-15');

        $period2From = new DateTime('2024-02-01');
        $period2To = new DateTime('2024-02-15');

        $year = $this->calendar2024->periodDates([$period1From, $period1To], [$period2From, $period2To]);

        $this->assertEquals(2024, $year->year);

        $periods = $year->getPeriods();
        $this->assertCount(2, $periods);

        $this->assertEquals('2024-01-01', $periods[0]->getFirstDay()->format('Y-m-d'));
        $this->assertEquals('2024-01-15', $periods[0]->getLastDay()->format('Y-m-d'));

        $this->assertEquals('2024-02-01', $periods[1]->getFirstDay()->format('Y-m-d'));
        $this->assertEquals('2024-02-15', $periods[1]->getLastDay()->format('Y-m-d'));
    }

    public function testWorkingSaturdays(): void
    {
        $holidays = [
            new DateTime('2024-01-01'),
        ];

        $workingSaturdays = [
            new DateTime('2024-01-13')
        ];

        $calendar = new Calendar($holidays, $workingSaturdays);

        $workingSaturday = new DateTime('2024-01-13');
        $this->assertTrue($calendar->isWorkDay($workingSaturday));

        $regularSaturday = new DateTime('2024-01-20');
        $this->assertFalse($calendar->isWorkDay($regularSaturday));
    }

    public function testInvalidDateInConstructor(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Empty red days');

        new Calendar([]);
    }
}