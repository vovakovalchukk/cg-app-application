<?php
namespace Reports\OrderCount;

use CG\Stdlib\DateTime;

class UnitService
{
    const UNIT_DAY = 'day';
    const UNIT_MONTH = 'month';
    const UNIT_WEEK = 'week';

    const UNIT_MAP = [
        self::UNIT_DAY => 'P1D',
        self::UNIT_MONTH => 'P1M',
        self::UNIT_WEEK => 'P1W'
    ];

    const UNIT_CONVERSION_MAP = [
        self::UNIT_DAY => DateTime::FORMAT_DATE,
        self::UNIT_MONTH => 'M-Y',
        self::UNIT_WEEK => [UnitService::class, 'formatWeek']
    ];

    public static function formatWeek(DateTime $dateTime)
    {
        $time = strtotime('last monday', ($dateTime->add(new \DateInterval('P1D')))->getTimestamp());
        return  (new DateTime())
            ->setTimestamp($time)
            ->format(DateTime::FORMAT_DATE);
    }

    public function buildDateUnitFromDates(DateTime $start, DateTime $end): array
    {
        $dateUnits = [];
        foreach (static::UNIT_MAP as $unit => $interval) {
            $period = new \DatePeriod(
                $start,
                new \DateInterval($interval),
                $end
            );
            $dateUnits[$unit] = iterator_count($period);
        }
        return $dateUnits;
    }

    public function createZeroFilledArray(DateTime $start, DateTime $end, string $unit, array $subKeys)
    {
        $unit = $this->validateUnit($unit);

        $period = new \DatePeriod(
            $start,
            new \DateInterval($this->getIntervalByUnit($unit)),
            $end
        );

        $result = [];
        foreach ($period as $dateTime) {
            foreach ($subKeys as $subKey) {
                $result[$this->formatUnitForEntity($dateTime, $unit)][$subKey] = 0;
            }
        }
        return $result;
    }

    public function formatUnitForEntityFromString(string $date, string $unit)
    {
        return $this->formatUnitForEntity(new DateTime($date), $unit);
    }

    public function formatUnitForEntity(DateTime $dateTime, string $unit)
    {
        $unit = $this->validateUnit($unit);
        $unitFormat = self::UNIT_CONVERSION_MAP[$unit];
        if (is_callable($unitFormat)) {
            return $unitFormat($dateTime);
        }

        return $dateTime->format($unitFormat);
    }

    public function validateUnit(string $unit)
    {
        if (empty($unit) || !isset(static::UNIT_MAP[$unit])) {
            return self::UNIT_DAY;
        }

        return $unit;
    }

    protected function getIntervalByUnit(string $unit)
    {
        return static::UNIT_MAP[$unit];
    }
}
