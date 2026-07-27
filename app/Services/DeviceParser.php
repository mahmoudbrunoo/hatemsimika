<?php

namespace App\Services;

/** استخراج نوع الجهاز ونظام التشغيل والمتصفح من الـ User Agent */
class DeviceParser
{
    /** @return array{device_type: string, device_name: string, os: string, browser: string} */
    public static function parse(?string $userAgent): array
    {
        $ua = $userAgent ?? '';

        $deviceType = 'Desktop';
        if (preg_match('/iPad|Tablet/i', $ua)) {
            $deviceType = 'Tablet';
        } elseif (preg_match('/Mobile|Android|iPhone/i', $ua)) {
            $deviceType = 'Mobile';
        }

        $os = 'Unknown';
        foreach ([
            'Windows NT 11' => 'Windows 11', 'Windows NT 10' => 'Windows 10',
            'Windows' => 'Windows', 'Android' => 'Android',
            'iPhone OS|iOS' => 'iOS', 'Mac OS X' => 'macOS', 'Linux' => 'Linux',
        ] as $pattern => $label) {
            if (preg_match('/' . $pattern . '/i', $ua)) {
                $os = $label;
                break;
            }
        }

        $browser = 'Unknown';
        foreach ([
            'Edg' => 'Edge', 'OPR|Opera' => 'Opera', 'SamsungBrowser' => 'Samsung Internet',
            'Chrome' => 'Chrome', 'Safari' => 'Safari', 'Firefox' => 'Firefox',
        ] as $pattern => $label) {
            if (preg_match('/(' . $pattern . ')\/?\s?([\d.]*)/i', $ua, $m)) {
                $version = isset($m[2]) ? explode('.', $m[2])[0] : '';
                $browser = trim($label . ' ' . $version);
                break;
            }
        }

        return [
            'device_type' => $deviceType,
            'device_name' => $deviceType === 'Desktop' ? 'Unknown' : ($deviceType === 'Tablet' ? 'Tablet' : 'Phone'),
            'os' => $os,
            'browser' => $browser,
        ];
    }
}
