<?php

namespace App\Service;

use DateTime;

class WorkingDaysCounterService
{
    public static function countWorkingDays(\DateTimeInterface $fromDate, \DateTimeInterface $toDate): int
    {

        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $workingDays = 0;

        $currentDate = clone $fromDate;

        while ($currentDate <= $toDate) {
            if (self::isWorkingDay($currentDate)) {
                $workingDays++;
            }
            $currentDate->modify('+1 day');
        }

        return $workingDays;
    }

    private static function isWorkingDay(\DateTimeInterface $date): bool
    {
        return $date->format('N') < 6;
    }

}