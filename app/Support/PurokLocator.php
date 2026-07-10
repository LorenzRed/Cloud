<?php

namespace App\Support;

use App\Models\Barangay;
use App\Models\Purok;

class PurokLocator
{
    public const NON_NAME = 'Non';

    public static function ensureNonPurok(Barangay $barangay): Purok
    {
        return Purok::firstOrCreate(
            ['barangay_id' => $barangay->id, 'name' => self::NON_NAME],
            ['created_by' => null, 'boundary_geojson' => null]
        );
    }

    public static function resolveForPoint(Barangay $barangay, float $lat, float $lng): Purok
    {
        $matched = Purok::query()
            ->where('barangay_id', $barangay->id)
            ->whereNotNull('boundary_geojson')
            ->where('name', '!=', self::NON_NAME)
            ->get()
            ->first(function (Purok $purok) use ($lat, $lng) {
                return is_array($purok->boundary_geojson)
                    && GeoBoundary::geometryContainsPoint($purok->boundary_geojson, $lat, $lng);
            });

        return $matched ?? self::ensureNonPurok($barangay);
    }
}
