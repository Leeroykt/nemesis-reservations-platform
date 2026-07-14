/**
 * Timezone Utilities
 * Handles date/time formatting with the restaurant's timezone.
 * Ref: 09d-TIMEZONE-HANDLING.md
 */

export function formatDate(date: string, timezone: string, format: string = 'Y-m-d'): string {
  // Simple formatting using Intl.DateTimeFormat
  // This is a placeholder; we can use date-fns-tz if needed.
  const d = new Date(date + 'T00:00:00');
  const options: Intl.DateTimeFormatOptions = { timeZone: timezone };
  switch (format) {
    case 'Y-m-d':
      return d.toLocaleDateString('en-CA', options); // YYYY-MM-DD
    case 'M d, Y':
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', timeZone: timezone });
    default:
      return d.toLocaleDateString('en-US', options);
  }
}

export function formatTime(time: string, timezone: string): string {
  // time is like "19:00"
  const d = new Date(`1970-01-01T${time}:00`);
  return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', timeZone: timezone });
}