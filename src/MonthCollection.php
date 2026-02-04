<?php

namespace Kim1ne\ProductionCalendar;

use Traversable;

class MonthCollection implements \IteratorAggregate
{
    /**
     * @param Month[] $months
     */
    public function __construct(
        private array $months = []
    ) {}

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->months);
    }

    public function add(Month $month): void
    {
        $this->months[] = $month;
    }

    public function getMonth(int $month): ?Month
    {
        foreach ($this->months as $productionMonth) {
            if ($productionMonth->month === $month) {
                return $productionMonth;
            }
        }

        return null;
    }

    public function getMonths(): array
    {
        return $this->months;
    }
}