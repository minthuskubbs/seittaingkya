<?php

if (! function_exists('money')) {
    /** Format an amount in the app currency (MMK). */
    function money($amount): string
    {
        $currency = config('app.currency', 'MMK');

        return number_format((float) $amount, 0).' '.$currency;
    }
}

if (! function_exists('current_clinic')) {
    function current_clinic()
    {
        return auth()->user()?->clinic;
    }
}

if (! function_exists('device_from_agent')) {
    /**
     * Best-effort human-readable device from a User-Agent string, e.g.
     * "Windows · Chrome" or "iPhone · Safari". (Note: browsers report both
     * Windows 10 and 11 as "Windows NT 10.0", so they can't be told apart here.)
     */
    function device_from_agent(?string $ua): string
    {
        if (! $ua) {
            return 'Unknown device';
        }

        // Operating system.
        $os = 'Unknown OS';
        if (preg_match('/Windows NT 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/iPhone/i', $ua)) {
            $os = 'iPhone';
        } elseif (preg_match('/iPad/i', $ua)) {
            $os = 'iPad';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/Mac OS X/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        // Browser (order matters: Edge/Chrome contain "Safari").
        $browser = 'Unknown browser';
        if (preg_match('/Edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR|Opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/i', $ua)) {
            $browser = 'Safari';
        }

        return $os.' · '.$browser;
    }
}
