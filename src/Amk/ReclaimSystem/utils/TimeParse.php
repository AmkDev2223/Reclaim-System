<?php

namespace Amk\ReclaimSystem\utils;

class TimeParse {

    public static function parse(string $timeString): int {
        $timeString = strtolower(trim($timeString));
        $pattern = '/(\d+)([dhms])/';
        preg_match_all($pattern, $timeString, $matches, PREG_SET_ORDER);
        $seconds = 0;
        foreach ($matches as $match) {
            $value = (int)$match[1];
            $unit = $match[2];
            switch ($unit) {
                case 'd':
                    $seconds += $value * 86400;
                    break;
                case 'h':
                    $seconds += $value * 3600;
                    break;
                case 'm':
                    $seconds += $value * 60;
                    break;
                case 's':
                    $seconds += $value;
                    break;
            }
        }
        return $seconds;
    }
}