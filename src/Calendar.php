<?php

namespace Kim1ne\ProductionCalendar;

use DateTime;

class Calendar
{
    private ?int $year = null;

    private array $redDates;
    private array $workingSaturdays = [];

    private array $shortDays = [];
    private array $workingDays = [];

    private ?Year $mainYear = null;

    private DateTime $firstDayInYear;
    private DateTime $lastDayInYear;

    private array $month2allDays = [];

    const ORDINARY_HOURS_DAY = 8;
    const SHORT_HOURS_DAY = self::ORDINARY_HOURS_DAY - 1;

    /**
     * @param DateTime[] $holidays
     * @param DateTime[] $workingSaturdays
     */
    public function __construct(array $holidays, array $workingSaturdays = [], array $shortDays = [])
    {
        $this->prepareDates($holidays, 'red');
        $this->prepareDates($workingSaturdays, 'workingSaturdays');
        $this->prepareDates($shortDays, 'shortDays');

        $this->firstDayInYear = new DateTime("01.01.{$this->year}");
        $this->lastDayInYear = new DateTime("31.12.{$this->year}");

        $this->prepareWorkingDates();

        for ($month = 1; $month <= 12; $month++) {
            $firstDay = new DateTime(sprintf('%04d-%02d-01', $this->year, $month));
            $this->month2allDays[$month] = (int)$firstDay->format('t');
        }
    }

    private function prepareWorkingDates(): void
    {
        $date = clone $this->firstDayInYear;
        $interval = new \DateInterval('P1D');

        $this->workingDays = [];

        while ((int) $date->format('Y') === $this->year) {
            $month = (int) $date->format('m');
            $day = (int) $date->format('d');
            $dayOfWeek = (int) $date->format('N');

            if ($dayOfWeek === 7) {
                $date->add($interval);
                continue;
            }

            $isHoliday = isset($this->redDates[$month][$day]);

            if ($dayOfWeek === 6) {
                if ($isHoliday) {
                    $date->add($interval);
                    continue;
                }

                if (isset($this->workingSaturdays[$month][$day])) {
                    $this->workingDays[$month][$day] = true;
                    $date->add($interval);
                    continue;
                }

                $date->add($interval);
                continue;
            }

            if (!$isHoliday) {
                $this->workingDays[$month][$day] = true;
            }

            $date->add($interval);
        }
    }

    private function firstDayInYear(): DateTime
    {
        return $this->firstDayInYear;
    }

    private function lastDayInYear(): DateTime
    {
        return $this->lastDayInYear;
    }

    /**
     * @param DateTime[] $days
     * @param bool $require
     */
    private function prepareDates(array $days, string $type): void
    {
        $isRed = $type === 'red';

        if ($isRed && empty($days)) {
            throw new \Exception('Empty red days');
        }

        if ($isRed) {
            $date = $days[0];

            if (
                ($date instanceof DateTime) === false
            ) {
                throw new \Exception('Invalid date');
            }

            $this->year = (int) $date->format('Y');
        }

        foreach ($days as $day) {
            $this->isValidDateOrThrow($day);

            $month = (int) $day->format('m');
            $intDay = (int) $day->format('d');

            if ($isRed) {
                $this->redDates[$month][$intDay] = true;
            } elseif ($type === 'workingSaturdays') {
                $this->workingSaturdays[$month][$intDay] = true;
            } else {
                $this->shortDays[$month][$intDay] = true;
            }
        }
    }

    private function isValidDateOrThrow($date): void
    {
        if (
            ($date instanceof DateTime) === false
        ) {
            throw new \Exception('Invalid date');
        }

        if ($this->year !== null) {
            $year = (int) $date->format('Y');

            if ($year !== $this->year) {
                throw new \Exception(sprintf(
                    'Invalid date, because the year must be a %s. Your the year %s', $this->year, $year
                ));
            }
        }
    }

    public function isShortDay(DateTime $date): bool
    {
        $month = (int) $date->format('m');
        $day = (int) $date->format('d');

        return ($this->shortDays[$month][$day] ?? false) === true;
    }

    public function isWorkDay(DateTime $date): bool
    {
        $month = (int) $date->format('m');
        $day = (int) $date->format('d');
        return ($this->workingDays[$month][$day] ?? false) === true;
    }

    public function period(DateTime $from, ?DateTime $to): Year
    {
        $reportStart = $this->firstDayInYear;
        $reportEnd = $this->lastDayInYear;

        if ($from->getTimestamp() < $reportStart->getTimestamp()) {
            $calcFrom = clone $reportStart;
        } else {
            $calcFrom = clone $from;
        }

        if ($to === null) {
            $calcTo = clone $reportEnd;
        } else {
            if ($to->getTimestamp() < $reportStart->getTimestamp()) {
                return new Year($this->year);
            }

            $calcTo = ($to->getTimestamp() > $reportEnd->getTimestamp())
                ? clone $reportEnd
                : clone $to;
        }

        if ($calcFrom->getTimestamp() > $calcTo->getTimestamp()) {
            return new Year($this->year);
        }

        $collection = new MonthCollection();
        $productionYear = new Year($this->year, $collection, [new Period($from, $to)]);

        $current = clone $calcFrom;

        $interval1day = new \DateInterval('P1D');

        $workData = [];
        $lastMonth = (int) $calcTo->format('m');
        $lastDay = (int) $calcTo->format('d');

        while ($current->getTimestamp() <= $calcTo->getTimestamp()) {
            $month = (int) $current->format('m');
            $day = (int) $current->format('d');
            $allDaysInMonth = $this->month2allDays[$month];

            $isWorkingDay = $this->isWorkDay($current);

            if ($isWorkingDay) {
                if (!isset($workData[$month])) {
                    $workData[$month] = [
                        'days' => 0,
                        'hours' => 0,
                        'shortDays' => []
                    ];
                }

                $isShortDay = $this->isShortDay($current);

                $workData[$month]['days']++;
                $workData[$month]['hours'] += $isShortDay ? self::SHORT_HOURS_DAY : self::ORDINARY_HOURS_DAY;

                if ($isShortDay) {
                    $workData[$month]['shortDays'][] = clone $current;
                }
            }

            if (
                !empty($workData[$month]['days']) &&
                (
                    ($day === $allDaysInMonth) ||
                    ($month === $lastMonth && $day === $lastDay)
                )
            ) {
                $dates = [];

                foreach ($this->redDates[$month] ?? [] as $day) {
                    $dates[] = new DateTime("{$day}.{$month}.{$this->year}");
                }

                $productionMonth = new Month(
                    $this->year,
                    $month,
                    $workData[$month]['days'] ?? 0,
                    $workData[$month]['hours'] ?? 0,
                    $workData[$month]['shortDays'] ?? [],
                    $dates
                );

                $collection->add($productionMonth);
            }

            $current->add($interval1day);
        }

        return $productionYear;
    }

    public function getYear(): Year
    {
        if ($this->mainYear === null) {
            $this->mainYear = $this->period($this->firstDayInYear(), $this->lastDayInYear());
        }

        return $this->mainYear;
    }

    /**
     * @param array<array<DateTime, DateTime>> $between
     * @return Year
     */
    public function periodDates(array ...$periodDates): Year
    {
        $monthAggregator = [];

        $periods = [];
        foreach ($periodDates as [$from, $to]) {

            $year = $this->period($from, $to);
            $periods[] = new Period($from, $to);

            foreach ($year as $monthData) {
                if (!isset($monthAggregator[$monthData->month])) {
                    $monthAggregator[$monthData->month] = $monthData;
                } else {
                    $existing = $monthAggregator[$monthData->month];

                    $monthAggregator[$monthData->month] = new Month(
                        $monthData->year,
                        $monthData->month,
                        $existing->workingDays + $monthData->workingDays,
                        $existing->hours + $monthData->hours,
                        array_merge($existing->shortDays, $monthData->shortDays),
                        array_merge($existing->holidays, $monthData->holidays)
                    );
                }
            }
        }

        ksort($monthAggregator);
        $collection = new MonthCollection(array_values($monthAggregator));

        return new Year($this->year, $collection, $periods);
    }
}