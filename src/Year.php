<?php

namespace Kim1ne\ProductionCalendar;

use Traversable;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<int, Month>
 */
readonly class Year implements IteratorAggregate
{
    /**
     * @param int $year
     * @param MonthCollection $monthCollection
     * @param Period[] $periods
     */
    public function __construct(
        public int              $year,
        private MonthCollection $monthCollection = new MonthCollection(),
        private array           $periods = []
    ) {}

    /**
     * @return Period[]
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    public function getIterator(): Traversable
    {
        return $this->monthCollection->getIterator();
    }

    public function getHours(): int
    {
        $result = 0;

        foreach ($this as $month) {
            $result += $month->hours;
        }

        return $result;
    }

    public function getWorkingDays(): int
    {
        $result = 0;

        foreach ($this as $month) {
            $result += $month->workingDays;
        }

        return $result;
    }

    public function getShortDays(): int
    {
        $result = 0;

        foreach ($this as $month) {
            $result += count($month->shortDays);
        }

        return $result;
    }

    public function getMonthsCollection(): MonthCollection
    {
        return $this->monthCollection;
    }

    public function getMonths(): array
    {
        return $this->monthCollection->getMonths();
    }

    public function getMonth(int $month): ?Month
    {
        return $this->monthCollection->getMonth($month);
    }
}