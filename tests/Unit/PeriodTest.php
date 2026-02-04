<?php

namespace Kim1ne\ProductionCalendar\Tests\Unit;

use Kim1ne\ProductionCalendar\Period;
use PHPUnit\Framework\TestCase;
use DateTime;

class PeriodTest extends TestCase
{
    public function testPeriodCreation(): void
    {
        $firstDay = new DateTime('2024-01-01');
        $lastDay = new DateTime('2024-01-31');

        $period = new Period($firstDay, $lastDay);

        $this->assertInstanceOf(Period::class, $period);
        $this->assertEquals('2024-01-01', $period->getFirstDay()->format('Y-m-d'));
        $this->assertEquals('2024-01-31', $period->getLastDay()->format('Y-m-d'));
    }

    public function testCountDaysForSingleDayPeriod(): void
    {
        $date = new DateTime('2024-01-15');
        $period = new Period($date, $date);

        $this->assertEquals(1, $period->countDays());
    }

    public function testCountDaysForTwoDaysPeriod(): void
    {
        $firstDay = new DateTime('2024-01-15');
        $lastDay = new DateTime('2024-01-16');
        $period = new Period($firstDay, $lastDay);

        $this->assertEquals(2, $period->countDays());
    }

    public function testCountDaysForMonthPeriod(): void
    {
        $firstDay = new DateTime('2024-01-01');
        $lastDay = new DateTime('2024-01-31');
        $period = new Period($firstDay, $lastDay);

        $this->assertEquals(31, $period->countDays());
    }

    public function testCountDaysForYearPeriod(): void
    {
        $firstDay = new DateTime('2024-01-01');
        $lastDay = new DateTime('2024-12-31');
        $period = new Period($firstDay, $lastDay);

        $this->assertEquals(366, $period->countDays());
    }

    public function testCountDaysForLeapYearPeriod(): void
    {
        $firstDay = new DateTime('2024-01-01');
        $lastDay = new DateTime('2025-01-01');
        $period = new Period($firstDay, $lastDay);

        $this->assertEquals(367, $period->countDays());
    }

    public function testCountDaysWithTimeComponents(): void
    {
        $firstDay = new DateTime('2024-01-01 10:30:00');
        $lastDay = new DateTime('2024-01-02 14:45:00');
        $period = new Period($firstDay, $lastDay);

        $this->assertEquals(2, $period->countDays());
    }

    public function testCountDaysIsCached(): void
    {
        $firstDay = new DateTime('2024-01-01');
        $lastDay = new DateTime('2024-01-10');
        $period = new Period($firstDay, $lastDay);

        $firstResult = $period->countDays();
        $this->assertEquals(10, $firstResult);

        $secondResult = $period->countDays();
        $this->assertEquals(10, $secondResult);

        $this->assertEquals('2024-01-01', $period->getFirstDay()->format('Y-m-d'));
        $this->assertEquals('2024-01-10', $period->getLastDay()->format('Y-m-d'));
    }

    public function testCountDaysWithSameDateTimeObject(): void
    {
        $date = new DateTime('2024-01-15');
        $period = new Period($date, clone $date);

        $this->assertEquals(1, $period->countDays());

        $this->assertEquals('2024-01-15', $date->format('Y-m-d'));
    }

    public function testCountDaysWithDateTimeModification(): void
    {
        $firstDay = new DateTime('2024-01-01');
        $lastDay = new DateTime('2024-01-05');
        $period = new Period($firstDay, $lastDay);

        $days = $period->countDays();
        $this->assertEquals(5, $days);

        $firstDay->modify('+1 month');
        $lastDay->modify('-1 month');

        $this->assertEquals(5, $period->countDays());
        $this->assertEquals('2024-02-01', $firstDay->format('Y-m-d'));
        $this->assertEquals('2024-01-01', $period->getFirstDay()->format('Y-m-d'));
    }

    public function testCountDaysForReversePeriod(): void
    {
        $firstDay = new DateTime('01.01.2024');
        $lastDay = new DateTime('10.01.2024');
        $period = new Period($firstDay, $lastDay);

        $this->assertEquals(10, $period->countDays());
    }

    public function testPeriodPropertiesAreReadonly(): void
    {
        $firstDay = new DateTime('2024-01-01');
        $lastDay = new DateTime('2024-01-31');

        $period = new Period($firstDay, $lastDay);

        $this->assertTrue($period->getFirstDay() instanceof DateTime);
        $this->assertTrue($period->getLastDay() instanceof DateTime);

        $this->assertEquals('2024-01-01', $period->getFirstDay()->format('Y-m-d'));
        $this->assertEquals('2024-01-31', $period->getLastDay()->format('Y-m-d'));
    }

    public function testCountDaysWithDifferentTimezones(): void
    {
        $timezone1 = new \DateTimeZone('Europe/Moscow');
        $timezone2 = new \DateTimeZone('UTC');

        $firstDay = new DateTime('2024-01-01 23:00:00', $timezone1);
        $lastDay = new DateTime('2024-01-02 01:00:00', $timezone2);

        $period = new Period($firstDay, $lastDay);

        $this->assertEquals(2, $period->countDays());
    }

    public function testLargePeriodCalculation(): void
    {
        $firstDay = new DateTime('01.01.2000');
        $lastDay = new DateTime('01.01.2030');
        $period = new Period($firstDay, $lastDay);

        $expectedDays = 30 * 365 + 8 + 1;
        $this->assertEquals($expectedDays, $period->countDays());

        $this->assertEquals(
            $expectedDays,
            $period->countDays(),
            "Cached result should match initial calculation"
        );
    }

    public function testCountDaysVariousPeriods(): void
    {
        $date = new DateTime('01.01.2024');
        $period = new Period(
            $date,
            clone $date
        );

        $this->assertEquals(1, $period->countDays());

        $period = new Period(
            new DateTime('01.01.2024'),
            new DateTime('08.01.2024')
        );
        $this->assertEquals(8, $period->countDays());

        $period = new Period(
            new DateTime('01.01.2024'),
            new DateTime('01.02.2024')
        );

        $this->assertEquals(32, $period->countDays());

        $period = new Period(
            new DateTime('01.01.2023'),
            new DateTime('01.01.2024')
        );

        $this->assertEquals(366, $period->countDays());

        $period = new Period(
            new DateTime('01.01.2024'),
            new DateTime('01.01.2025')
        );
        $this->assertEquals(367, $period->countDays());
    }

    public function testCountDaysCaching(): void
    {
        $period = new Period(
            new DateTime('01.01.2024'),
            new DateTime('10.01.2024')
        );

        $firstResult = $period->countDays();
        $this->assertEquals(10, $firstResult);

        $secondResult = $period->countDays();
        $this->assertEquals($firstResult, $secondResult);
    }

    public function testModificationDateInPeriod()
    {
        $firstDay = new DateTime('01.01.2024');
        $lastDay = new DateTime('10.01.2024');

        $period = new Period(
            $firstDay,
            $lastDay
        );

        $firstDay->modify('+1 month');
        $lastDay->modify('+1 month');

        $period->getFirstDay()->modify('+1 month');
        $period->getLastDay()->modify('+1 month');

        $this->assertEquals($firstDay->format('d.m.Y'), '01.02.2024');
        $this->assertEquals($lastDay->format('d.m.Y'), '10.02.2024');

        $this->assertEquals($period->getFirstDay()->format('d.m.Y'), '01.01.2024');
        $this->assertEquals($period->getLastDay()->format('d.m.Y'), '10.01.2024');
    }
}