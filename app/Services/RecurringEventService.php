<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurringEventService
{
    /**
     * Get the next upcoming Online Day
     */
    public static function getNextOnlineDay(): ?array
    {
        $config = config('online-day');
        
        if (!$config || !$config['enabled']) {
            return null;
        }
        
        $today = Carbon::today();
        $dayOfWeek = $config['day_of_week'];
        
        // Calculate next occurrence
        $nextDate = $today->copy();
        
        // If today is the day and we haven't passed the start time, use today
        if ($today->dayOfWeekIso === $dayOfWeek && Carbon::now()->format('H:i:s') < $config['time_start']) {
            // Use today
        } else {
            // Find next occurrence
            $nextDate = $today->copy()->next($dayOfWeek);
        }
        
        return self::buildOnlineDayEvent($nextDate, $config);
    }
    
    /**
     * Get Online Days for the next X months from today
     * 
     * @param int $monthsAhead Number of complete months ahead to generate (default: 4)
     */
    public static function getUpcomingOnlineDays(int $monthsAhead = 4): Collection
    {
        $config = config('online-day');
        
        if (!$config || !$config['enabled']) {
            return collect();
        }
        
        $today = Carbon::today();
        $dayOfWeek = $config['day_of_week'];
        $events = collect();
        
        // Calculate end date: end of the month X months ahead
        $endDate = $today->copy()->addMonths($monthsAhead)->endOfMonth();
        
        // Start from today or next occurrence
        $currentDate = $today->copy();
        
        // If today is not the Online Day, move to next occurrence
        if ($currentDate->dayOfWeekIso !== $dayOfWeek) {
            $currentDate = $currentDate->next($dayOfWeek);
        }
        
        // Generate all occurrences until end date
        while ($currentDate->lte($endDate)) {
            $events->push(self::buildOnlineDayEvent($currentDate->copy(), $config));
            $currentDate->addWeek();
        }
        
        return $events;
    }
    
    /**
     * Get Online Days for the calendar view (past + future)
     * 
     * @param int $monthsBefore Number of complete months before to generate (default: 3)
     * @param int $monthsAhead Number of complete months ahead to generate (default: 4)
     */
    public static function getCalendarOnlineDays(int $monthsBefore = 3, int $monthsAhead = 4): Collection
    {
        $config = config('online-day');
        
        if (!$config || !$config['enabled']) {
            return collect();
        }
        
        $today = Carbon::today();
        $dayOfWeek = $config['day_of_week'];
        $events = collect();
        
        // Calculate start date: first day of the month X months ago
        $startDate = $today->copy()->startOfMonth()->subMonths($monthsBefore);
        
        // Calculate end date: end of the month X months ahead
        $endDate = $today->copy()->addMonths($monthsAhead)->endOfMonth();
        
        // Find first occurrence from start date
        $currentDate = $startDate->copy();
        if ($currentDate->dayOfWeekIso !== $dayOfWeek) {
            $currentDate = $currentDate->next($dayOfWeek);
        }
        
        // Generate all occurrences from start to end
        while ($currentDate->lte($endDate)) {
            $events->push(self::buildOnlineDayEvent($currentDate->copy(), $config));
            $currentDate->addWeek();
        }
        
        return $events;
    }
    
    /**
     * Build an Online Day event array
     */
    private static function buildOnlineDayEvent(Carbon $date, array $config): array
    {
        return [
            'title' => $config['title'],
            'date' => $date,
            'time_start' => $config['time_start'],
            'time_end' => $config['time_end'],
            'type' => $config['type'],
            'illustration' => $config['illustration'],
            'description' => $config['description'],
            'is_recurring' => true, // Flag to identify recurring events
        ];
    }
}