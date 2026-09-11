<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Recommends a locker size from a package's weight/dimensions, matching the
 * S/M/L/XL descriptions shown throughout the app (envelope/shoebox/carry-on/
 * large box). Whichever dimension pushes it into a bigger bracket wins.
 */
class LockerSizeRecommender
{
    private const THRESHOLDS = [
        // size => [max weight kg, max longest side cm]
        'S'  => [3, 35],
        'M'  => [8, 45],
        'L'  => [15, 60],
        'XL' => [25, 80],
    ];

    /** @return string one of S, M, L, XL — or null if it exceeds even XL. */
    public static function recommend(?float $weightKg, ?float $lengthCm, ?float $widthCm, ?float $heightCm): ?string
    {
        $longestSide = max(array_filter([$lengthCm, $widthCm, $heightCm], static fn ($v) => $v !== null));

        foreach (self::THRESHOLDS as $size => [$maxWeight, $maxSide]) {
            $weightOk = $weightKg === null || $weightKg <= $maxWeight;
            $sideOk   = $longestSide === false || $longestSide <= $maxSide;

            if ($weightOk && $sideOk) {
                return $size;
            }
        }

        return null;
    }
}
