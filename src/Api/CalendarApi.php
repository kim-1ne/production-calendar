<?php

namespace Kim1ne\ProductionCalendar\Api;

class CalendarApi implements CalendarApiDataInterface
{
    private int $year = 0;

    private static array $years = [];

    public function getUrl(): string
    {
        return sprintf('https://calendar.kuzyak.in/api/calendar/%d/holidays', $this->year);
    }

    private function curlInit(): \CurlHandle
    {
        $url = $this->getUrl();

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'ProductionCalendar/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        return $ch;
    }

    public function getData(): CalendarReferenceData
    {
        $yearsData = self::$years[$this->year] ?? [];

        if (empty($yearsData)) {
            $ch = $this->curlInit();

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error) {
                throw new \RuntimeException(sprintf(
                    'CURL error for year %d: %s',
                    $this->year,
                    $error
                ));
            }

            if ($httpCode !== 200) {
                throw new \RuntimeException(sprintf(
                    'API returned HTTP %d for year %d',
                    $httpCode,
                    $this->year
                ));
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException(sprintf(
                    'Invalid JSON response for year %d: %s',
                    $this->year,
                    json_last_error_msg()
                ));
            }

            self::$years[$this->year] = $data;
        } else {
            $data = $yearsData;
        }

        $shortDaysProductionCalendar = [];
        $holidaysProductionCalendar = [];

        foreach ($data['shortDays'] ?? [] as $day) {
            $shortDaysProductionCalendar[] = new \DateTime($day['date']);
        }

        foreach ($data['holidays'] ?? [] as $day) {
            $holidaysProductionCalendar[] = new \DateTime($day['date']);
        }

        return new CalendarReferenceData(shortDays: $shortDaysProductionCalendar, holidays: $holidaysProductionCalendar);
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }
}