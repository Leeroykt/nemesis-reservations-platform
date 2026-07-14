import React from 'react';
import { formatTime } from '@/lib/timezone';

interface Reservation {
  id: number;
  guest_name: string;
  date: string;
  time: string;
  party_size: number;
  status: string;
}

interface MonthViewProps {
  year: number;
  month: number;
  grouped: Record<string, Reservation[]>;
  timezone: string;
}

export default function MonthView({ year, month, grouped, timezone }: MonthViewProps) {
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const today = new Date().toISOString().split('T')[0];

  const days: ({ date: string; day: number; isToday: boolean } | null)[] = [];
  for (let i = 0; i < firstDay; i++) {
    days.push(null);
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    days.push({ date: dateStr, day: d, isToday: dateStr === today });
  }

  const getStatusClass = (status: string): string => {
    switch (status) {
      case 'Confirmed': return 'status-confirmed';
      case 'Cancelled': return 'status-cancelled';
      case 'Completed': return 'status-completed';
      default: return 'status-upcoming';
    }
  };

  return (
    <div className="card-elev p-3">
      <div className="cal-grid">
        {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((day) => (
          <div key={day} className="text-center text-faint small fw-bold py-2">
            {day}
          </div>
        ))}
        {days.map((cell, index) => {
          if (!cell) {
            return <div key={`empty-${index}`} className="cal-cell muted" style={{ minHeight: '80px' }} />;
          }
          const dayReservations = grouped[cell.date] || [];
          const display = dayReservations.slice(0, 3);
          const extra = dayReservations.length - 3;

          return (
            <div key={cell.date} className={`cal-cell ${cell.isToday ? 'today' : ''}`}>
              <div className="cal-day-num">{cell.day}</div>
              {display.map((res) => (
                <div
                  key={res.id}
                  className={`cal-chip ${getStatusClass(res.status)}`}
                  style={{
                    background: 'var(--surface-2)',
                    padding: '2px 6px',
                    borderRadius: '4px',
                    fontSize: '.7rem',
                    marginTop: '4px',
                  }}
                >
                  {formatTime(res.time, timezone)} – {res.guest_name} ({res.party_size})
                </div>
              ))}
              {extra > 0 && (
                <div className="cal-chip text-muted-soft" style={{ fontSize: '.7rem', marginTop: '4px' }}>
                  +{extra} more
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}