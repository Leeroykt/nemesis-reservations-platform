/**
 * Timezone Utilities
 * Ref: 09d-TIMEZONE-HANDLING.md
 * 
 * NOTE: These functions are designed to work with the backend date/time format:
 * - Dates: 'YYYY-MM-DD' (string) from the database
 * - Times: 'HH:mm' or 'HH:mm:ss' from the database
 * 
 * The backend stores dates as strings (not Carbon objects) after our fix,
 * so these functions handle string inputs safely.
 */

/**
 * Format a date string to a localized date string.
 * 
 * @param date - Date string in YYYY-MM-DD format (from backend)
 * @param timezone - Restaurant timezone (e.g., 'Africa/Harare')
 * @param format - 'short' (e.g., "11 Jul 2026") or 'long' (e.g., "July 11, 2026")
 * @returns Formatted date string
 * 
 * @example
 * formatDate('2026-07-16', 'Africa/Harare', 'short')
 * // Returns: "16 Jul 2026"
 */
export function formatDate(
  date: string, 
  timezone: string, 
  format: 'short' | 'long' = 'short'
): string {
  // Backend sends 'YYYY-MM-DD' - we add time to avoid timezone shifting
  const d = new Date(date + 'T00:00:00');
  
  // If invalid, return the original string
  if (isNaN(d.getTime())) {
    return date;
  }
  
  if (format === 'short') {
    // e.g. "16 Jul 2026"
    return d.toLocaleDateString('en-GB', { 
      day: 'numeric', 
      month: 'short', 
      year: 'numeric', 
      timeZone: timezone 
    });
  } else {
    // e.g. "July 16, 2026"
    return d.toLocaleDateString('en-US', { 
      month: 'long', 
      day: 'numeric', 
      year: 'numeric', 
      timeZone: timezone 
    });
  }
}

/**
 * Format a time string to a localized time string.
 * 
 * @param time - Time string in HH:mm or HH:mm:ss format (from backend)
 * @param timezone - Restaurant timezone (e.g., 'Africa/Harare')
 * @returns Formatted time string (e.g., "7:30 PM")
 * 
 * @example
 * formatTime('19:30', 'Africa/Harare')
 * // Returns: "7:30 PM"
 * 
 * @example
 * formatTime('19:30:00', 'Africa/Harare')
 * // Returns: "7:30 PM" (handles seconds too)
 */
export function formatTime(time: string, timezone: string): string {
  // Extract HH:mm from the time string (handles HH:mm:ss too)
  const cleanTime = time.substring(0, 5);
  const d = new Date(`1970-01-01T${cleanTime}:00`);
  
  // If invalid, return the original string
  if (isNaN(d.getTime())) {
    return time;
  }
  
  return d.toLocaleTimeString('en-US', { 
    hour: 'numeric', 
    minute: '2-digit', 
    timeZone: timezone 
  });
}

/**
 * Format a date and time together.
 * 
 * @param date - Date string in YYYY-MM-DD format
 * @param time - Time string in HH:mm or HH:mm:ss format
 * @param timezone - Restaurant timezone
 * @returns Formatted date-time string
 * 
 * @example
 * formatDateTime('2026-07-16', '19:30', 'Africa/Harare')
 * // Returns: "16 Jul 2026, 7:30 PM"
 */
export function formatDateTime(
  date: string, 
  time: string, 
  timezone: string
): string {
  const formattedDate = formatDate(date, timezone, 'short');
  const formattedTime = formatTime(time, timezone);
  return `${formattedDate}, ${formattedTime}`;
}