<?php

namespace App\Support;

class GeoBoundary
{
    private const EDGE_EPSILON = 1e-6;

    public static function geometryWithinBoundary(array $geometry, array $boundary): bool
    {
        $geometryType = $geometry['type'] ?? '';

        if ($geometryType === 'FeatureCollection') {
            foreach ($geometry['features'] ?? [] as $feature) {
                if (! self::geometryWithinBoundary($feature, $boundary)) {
                    return false;
                }
            }

            return true;
        }

        if ($geometryType === 'Feature') {
            return self::geometryWithinBoundary($geometry['geometry'] ?? [], $boundary);
        }

        if ($geometryType === 'Polygon') {
            $outerRing = $geometry['coordinates'][0] ?? [];

            foreach ($outerRing as $point) {
                $lng = (float) ($point[0] ?? 0);
                $lat = (float) ($point[1] ?? 0);

                if (! self::geometryContainsPoint($boundary, $lat, $lng)) {
                    return false;
                }
            }

            return true;
        }

        if ($geometryType === 'MultiPolygon') {
            foreach ($geometry['coordinates'] ?? [] as $polygon) {
                if (! self::geometryWithinBoundary(['type' => 'Polygon', 'coordinates' => $polygon], $boundary)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public static function geometryContainsPoint(array $geometry, float $lat, float $lng): bool
    {
        $geometryType = $geometry['type'] ?? '';
        $coordinates = $geometry['coordinates'] ?? [];

        if ($geometryType === 'FeatureCollection') {
            foreach ($geometry['features'] ?? [] as $feature) {
                if (self::geometryContainsPoint($feature, $lat, $lng)) {
                    return true;
                }
            }

            return false;
        }

        if ($geometryType === 'Feature') {
            return self::geometryContainsPoint($geometry['geometry'] ?? [], $lat, $lng);
        }

        if ($geometryType === 'Polygon') {
            $rings = is_array($coordinates) ? $coordinates : [];
            $outerRing = $rings[0] ?? [];

            if (self::pointInRing($lat, $lng, $outerRing)) {
                foreach (array_slice($rings, 1) as $hole) {
                    if (self::pointInRing($lat, $lng, $hole)) {
                        return false;
                    }
                }

                return true;
            }

            return self::pointOnRingEdge($lat, $lng, $outerRing);
        }

        if ($geometryType === 'MultiPolygon') {
            foreach ($coordinates as $polygon) {
                if (self::geometryContainsPoint(['type' => 'Polygon', 'coordinates' => $polygon], $lat, $lng)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function pointInRing(float $lat, float $lng, array $ring): bool
    {
        $inside = false;
        $vertexCount = count($ring);

        if ($vertexCount < 3) {
            return false;
        }

        for ($i = 0, $j = $vertexCount - 1; $i < $vertexCount; $j = $i++) {
            $current = $ring[$i] ?? [];
            $previous = $ring[$j] ?? [];

            $xi = (float) ($current[0] ?? 0);
            $yi = (float) ($current[1] ?? 0);
            $xj = (float) ($previous[0] ?? 0);
            $yj = (float) ($previous[1] ?? 0);

            $intersects = (($yi > $lat) !== ($yj > $lat))
                && ($lng < (($xj - $xi) * ($lat - $yi) / (($yj - $yi) !== 0.0 ? ($yj - $yi) : 0.0000001)) + $xi);

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    private static function pointOnRingEdge(float $lat, float $lng, array $ring, float $epsilon = self::EDGE_EPSILON): bool
    {
        $vertexCount = count($ring);

        if ($vertexCount < 2) {
            return false;
        }

        $epsilonSquared = $epsilon * $epsilon;

        for ($i = 0; $i < $vertexCount; $i++) {
            $current = $ring[$i] ?? [];
            $next = $ring[($i + 1) % $vertexCount] ?? [];
            $latA = (float) ($current[1] ?? 0);
            $lngA = (float) ($current[0] ?? 0);
            $latB = (float) ($next[1] ?? 0);
            $lngB = (float) ($next[0] ?? 0);
            $candidate = self::closestPointOnSegment($lat, $lng, $latA, $lngA, $latB, $lngB);
            $distanceSquared = (($candidate['lat'] - $lat) ** 2) + (($candidate['lng'] - $lng) ** 2);

            if ($distanceSquared <= $epsilonSquared) {
                return true;
            }
        }

        return false;
    }

    private static function closestPointOnSegment(float $lat, float $lng, float $latA, float $lngA, float $latB, float $lngB): array
    {
        $dx = $lngB - $lngA;
        $dy = $latB - $latA;

        if ($dx === 0.0 && $dy === 0.0) {
            return ['lat' => $latA, 'lng' => $lngA];
        }

        $t = (($lng - $lngA) * $dx + ($lat - $latA) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0.0, min(1.0, $t));

        return [
            'lat' => $latA + ($t * $dy),
            'lng' => $lngA + ($t * $dx),
        ];
    }
}
