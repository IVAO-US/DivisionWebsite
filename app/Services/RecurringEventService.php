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
        
        return self::buildRecurringEvent($nextDate, $config);
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
            $events->push(self::buildRecurringEvent($currentDate->copy(), $config));
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
            $events->push(self::buildRecurringEvent($currentDate->copy(), $config));
            $currentDate->addWeek();
        }
        
        return $events;
    }

    /**
     * Get the next upcoming SpecOps Online Day
     */
    public static function getNextSpecOpsOnlineDay(): ?array
    {
        $config = config('specops-online-day');
        
        if (!$config || !$config['enabled']) {
            return null;
        }
        
        $today = Carbon::today();
        $dayOfWeek = $config['day_of_week'];
        $nthWeek = $config['nth_week'];
        
        // Calculate next occurrence
        $nextDate = self::calculateNthWeekdayOfMonth($today, $dayOfWeek, $nthWeek);
        
        // If the calculated date is in the past or today but past start time, move to next month
        if ($nextDate->lt($today) || 
            ($nextDate->eq($today) && Carbon::now()->format('H:i:s') >= $config['time_start'])) {
            $nextMonth = $today->copy()->addMonth()->startOfMonth();
            $nextDate = self::calculateNthWeekdayOfMonth($nextMonth, $dayOfWeek, $nthWeek);
        }
        
        return self::buildRecurringEvent($nextDate, $config);
    }

    /**
     * Get SpecOps Online Days for the next X months from today
     * 
     * @param int $monthsAhead Number of complete months ahead to generate (default: 4)
     */
    public static function getUpcomingSpecOpsOnlineDays(int $monthsAhead = 4): Collection
    {
        $config = config('specops-online-day');
        
        if (!$config || !$config['enabled']) {
            return collect();
        }
        
        $today = Carbon::today();
        $dayOfWeek = $config['day_of_week'];
        $nthWeek = $config['nth_week'];
        $events = collect();
        
        // Calculate end date: end of the month X months ahead
        $endDate = $today->copy()->addMonths($monthsAhead)->endOfMonth();
        
        // Start from current month
        $currentMonth = $today->copy()->startOfMonth();
        
        // Generate all occurrences until end date
        while ($currentMonth->lte($endDate)) {
            $eventDate = self::calculateNthWeekdayOfMonth($currentMonth, $dayOfWeek, $nthWeek);
            
            // Only add if the event date is today or in the future
            if ($eventDate->gte($today)) {
                $events->push(self::buildRecurringEvent($eventDate->copy(), $config));
            }
            
            $currentMonth->addMonth();
        }
        
        return $events;
    }

    /**
     * Get SpecOps Online Days for the calendar view (past + future)
     * 
     * @param int $monthsBefore Number of complete months before to generate (default: 3)
     * @param int $monthsAhead Number of complete months ahead to generate (default: 4)
     */
    public static function getCalendarSpecOpsOnlineDays(int $monthsBefore = 3, int $monthsAhead = 4): Collection
    {
        $config = config('specops-online-day');
        
        if (!$config || !$config['enabled']) {
            return collect();
        }
        
        $today = Carbon::today();
        $dayOfWeek = $config['day_of_week'];
        $nthWeek = $config['nth_week'];
        $events = collect();
        
        // Calculate start date: first day of the month X months ago
        $startDate = $today->copy()->startOfMonth()->subMonths($monthsBefore);
        
        // Calculate end date: end of the month X months ahead
        $endDate = $today->copy()->addMonths($monthsAhead)->endOfMonth();
        
        // Start from start month
        $currentMonth = $startDate->copy();
        
        // Generate all occurrences from start to end
        while ($currentMonth->lte($endDate)) {
            $eventDate = self::calculateNthWeekdayOfMonth($currentMonth, $dayOfWeek, $nthWeek);
            
            // Add the event regardless of whether it's in the past or future (for calendar display)
            $events->push(self::buildRecurringEvent($eventDate->copy(), $config));
            
            $currentMonth->addMonth();
        }
        
        return $events;
    }

    /**
     * Calculate the Nth occurrence of a specific weekday in a given month
     * 
     * @param Carbon $date Any date within the target month
     * @param int $dayOfWeek Day of week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
     * @param int $nthWeek Which occurrence (1 = first, 2 = second, 3 = third, etc.)
     * @return Carbon The calculated date
     */
    private static function calculateNthWeekdayOfMonth(Carbon $date, int $dayOfWeek, int $nthWeek): Carbon
    {
        // Start at the first day of the month
        $firstDayOfMonth = $date->copy()->startOfMonth();
        
        // Find the first occurrence of the target day of week in this month
        $firstOccurrence = $firstDayOfMonth->copy();
        
        // Adjust dayOfWeek for Carbon (0 = Sunday in config, but Carbon uses ISO where Monday = 1)
        // Convert: Sunday(0) -> 7, Monday(1) -> 1, ..., Saturday(6) -> 6
        $carbonDayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;
        
        if ($firstOccurrence->dayOfWeekIso !== $carbonDayOfWeek) {
            // Move to the first occurrence of the target weekday
            while ($firstOccurrence->dayOfWeekIso !== $carbonDayOfWeek) {
                $firstOccurrence->addDay();
            }
        }
        
        // Add weeks to get to the Nth occurrence
        $targetDate = $firstOccurrence->copy()->addWeeks($nthWeek - 1);
        
        return $targetDate;
    }
    
    /**
     * Build an Online Day event array
     */
    private static function buildRecurringEvent(Carbon $date, array $config): array
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