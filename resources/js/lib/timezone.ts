/**
 * Timezone Utilities
 * Ref: 09d-TIMEZONE-HANDLING.md
 */

export function formatDate(date: string, timezone: string, format: 'short' | 'long' = 'short'): string {
  const d = new Date(date + 'T00:00:00');
  const options: Intl.DateTimeFormatOptions = { timeZone: timezone };
  if (format === 'short') {
    // e.g. "11 Jul 2026"
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone: timezone });
  } else {
    // e.g. "July 11, 2026"
    return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric', timeZone: timezone });
  }
}

export function formatTime(time: string, timezone: string): string {
  const d = new Date(`1970-01-01T${time}:00`);
  return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', timeZone: timezone });
}