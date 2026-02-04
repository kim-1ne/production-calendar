# Production Calendar PHP Package

[![License](https://img.shields.io/github/license/kim-1ne/production-calendar)](LICENSE)

PHP библиотека для работы с производственным календарём России. Позволяет получать информацию о рабочих днях, праздниках, сокращённых днях и рассчитывать рабочее время за различные периоды.

## Особенности

- 📅 Работа с производственным календарём России
- ⏱️ Расчёт рабочих часов и дней
- 🏭 Поддержка сокращённых предпраздничных дней
- 🔧 Гибкая система периодов и интервалов
- 🚀 Автоматическое кэширование данных
- 🌐 Интеграция с внешним API календаря
- 📊 Подробная статистика по месяцам и годам

## Установка

Установка через Composer:

```bash
composer require kim1ne/production-calendar
```

```php
use Kim1ne\ProductionCalendar\ProductionCalendarFactory;

$productionCalendar = ProductionCalendarFactory::create(2026);

// $year = $productionCalendar->getYear();

// OR

// $year = $productionCalendar->period(
//     new DateTime('12.01.2026'),
//     new DateTime('16.01.2026')
// );

// OR

$year = $productionCalendar->periodDates(
    [
        new DateTime('12.01.2026'),
        new DateTime('16.01.2026')
    ],
    [
        new DateTime('12.02.2026'),
        new DateTime('16.02.2026')
    ]
);



$hours = $year->getHours();
$workingDays = $year->getWorkingDays();
$shortDays = $year->getShortDays();
```