<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\DivisionSession;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class HeadlineService
{
    /**
     * Get the current headline data based on priority:
     * 1. Division Sessions (happening now)
     * 2. Online Day (happening now)
     * 3. MOTD (Message of the Day)
     * 4. null (no headline)
     */
    public static function getCurrentHeadline(): ?array
    {
        $cacheKey = 'current_headline';
        $cacheDuration = 60; // Cache for 1 minute

        return Cache::remember($cacheKey, $cacheDuration, function () {
            // Priority 1: Check for active Division Sessions
            $divisionSession = self::getActiveDivisionSession();
            if ($divisionSession) {
                return $divisionSession;
            }

            // Priority 2: Check for Online Day
            $onlineDay = self::getActiveOnlineDay();
            if ($onlineDay) {
                return $onlineDay;
            }

            // Priority 3: Check for MOTD
            $motd = self::getMotd();
            if ($motd) {
                return $motd;
            }

            // No headline
            return null;
        });
    }

    /**
     * Get active Division Session (happening now)
     */
    private static function getActiveDivisionSession(): ?array
    {
        $now = Carbon::now('UTC');
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        $session = DivisionSession::where('date', $today)
            ->where('time_start', '<=', $currentTime)
            ->where('time_end', '>=', $currentTime)
            ->orderBy('time_start')
            ->first();

        if (!$session) {
            return null;
        }

        return [
            'type' => 'division_session',
            'icon' => 'phosphor.calendar-star',
            'title' => 'Happening now!',
            'message' => $session->title,
            'link' => null, // Could add a link to session details if needed
        ];
    }

    /**
     * Get active Online Day (happening now)
     */
    private static function getActiveOnlineDay(): ?array
    {
        $config = config('online-day');

        if (!$config || !$config['enabled']) {
            return null;
        }

        $now = Carbon::now('UTC');
        $currentDayOfWeek = $now->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
        $currentTime = $now->format('H:i:s');
        
        $startTime = $config['time_start'];
        $endTime = $config['time_end'];

        // Handle overnight events (end time < start time)
        $isOvernight = $endTime < $startTime;

        if ($isOvernight) {
            // Online Day is active from start_time until 23:59:59 on the configured day
            // OR from 00:00:00 until end_time on the day after
            $isActiveToday = ($currentDayOfWeek === $config['day_of_week'] && $currentTime >= $startTime);
            $isActiveTomorrow = ($currentDayOfWeek === ($config['day_of_week'] % 7) + 1 && $currentTime <= $endTime);

            $isActive = $isActiveToday || $isActiveTomorrow;
        } else {
            // Same day event
            $isActive = ($currentDayOfWeek === $config['day_of_week'] 
                && $currentTime >= $startTime 
                && $currentTime <= $endTime);
        }

        if (!$isActive) {
            return null;
        }

        return [
            'type' => 'online_day',
            'icon' => 'phosphor.globe-hemisphere-west',
            'title' => 'Happening now!',
            'message' => sprintf(
                '%s is active between %s UTC and %s UTC.',
                $config['title'],
                substr($startTime, 0, 5),
                substr($endTime, 0, 5)
            ),
            'link' => null,
        ];
    }

    /**
     * Get MOTD (Message of the Day)
     */
    private static function getMotd(): ?array
    {
        $motd = AppSetting::get('headline_motd');

        if (empty($motd)) {
            return null;
        }

        return [
            'type' => 'motd',
            'icon' => 'phosphor.megaphone',
            'title' => 'Welcome!',
            'message' => $motd,
            'link' => null,
        ];
    }

    /**
     * Clear the headline cache
     */
    public static function clearCache(): void
    {
        Cache::forget('current_headline');
    }
}