<?php
namespace Reports\OrderCount;

use CG\Stdlib\DateTime;

class UnitService
{
    const UNIT_DAY = 'day';
    const UNIT_MONTH = 'month';

    const UNIT_MAP = [
        self::UNIT_DAY => 'P1D',
        self::UNIT_MONTH => 'P1M'
    ];

    const UNIT_CONVERSION_MAP = [
        self::UNIT_DAY => DateTime::FORMAT_DATE,
        self::UNIT_MONTH => 'M-Y'
    ];

    public function createZeroFilledArray(DateTime $start, DateTime $end, string $unit = '')
    {
        $unit = $this->validateUnit($unit);

        $period = new \DatePeriod(
            $start,
            new \DateInterval($this->getIntervalByUnit($unit)),
            $end
        );

        $result = [];
        foreach ($period as $dateTime) {
            $result[$this->formatUnitForEntity($dateTime, $unit)] = 0;
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
        return $dateTime->format(self::UNIT_CONVERSION_MAP[$unit]);
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
