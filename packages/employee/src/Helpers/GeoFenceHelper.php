<?php

namespace Quochao56\Employee\Helpers;

class GeoFenceHelper
{
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Tính khoảng cách giữa 2 tọa độ GPS sử dụng Haversine formula.
     * Trả về khoảng cách tính bằng mét.
     */
    public static function distanceInMeters(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Kiểm tra xem tọa độ có nằm trong bán kính cho phép của trung tâm không.
     */
    public static function isWithinRadius(
        float $latitude,
        float $longitude,
        ?float $centerLat = null,
        ?float $centerLon = null,
        ?int $radiusMeters = null
    ): bool {
        $centerLat ??= (float) settings('center_latitude');
        $centerLon ??= (float) settings('center_longitude');
        $radiusMeters ??= (int) settings('allowed_radius_meters', 50);

        $distance = self::distanceInMeters($latitude, $longitude, $centerLat, $centerLon);

        return $distance <= $radiusMeters;
    }

    /**
     * Kiểm tra xem IP có nằm trong danh sách WiFi nội bộ được phép không.
     */
    public static function isAllowedIp(string $ip): bool
    {
        $allowedIps = settings('office_wifi_ips', '');

        if (empty($allowedIps)) {
            return false;
        }

        $allowedList = array_map('trim', explode(',', $allowedIps));

        return in_array($ip, $allowedList, true);
    }

    /**
     * Xác thực vị trí check-in: trả về true nếu hợp lệ (GPS hoặc IP).
     * Nếu không hợp lệ trả về false → caller nên gắn cờ flagged_location.
     */
    public static function verifyLocation(
        ?float $latitude,
        ?float $longitude,
        ?string $ip = null
    ): bool {
        // Ưu tiên 1: GPS
        if ($latitude !== null && $longitude !== null) {
            return self::isWithinRadius($latitude, $longitude);
        }

        // Dự phòng: IP WiFi nội bộ
        if ($ip !== null) {
            return self::isAllowedIp($ip);
        }

        return false;
    }
}
