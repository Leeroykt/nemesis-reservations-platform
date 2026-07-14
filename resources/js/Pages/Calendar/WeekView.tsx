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

interface WeekViewProps {
  startDate: string;
  reservations: Reservation[];
  timezone: string;
}

export default function WeekView({ startDate, reservations, timezone }: WeekViewProps) {
  const start = new Date(startDate);
  const days: Date[] = [];
  for (let i = 0; i < 7; i++) {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    days.push(d);
  }

  const hours = Array.from({ length: 15 }, (_, i) => i + 8);

  const grouped: Record<string, Reservation[]> = {};
  reservations.forEach((r) => {
    const idx = days.findIndex((d) => d.toISOString().split('T')[0] === r.date);
    if (idx === -1) return;
    const hourKey = r.time.split(':')[0];
    const key = `${idx}-${hourKey}`;
    if (!grouped[key]) grouped[key] = [];
    grouped[key].push(r);
  });

  const getStatusColor = (status: string): string => {
    switch (status) {
      case 'Confirmed': return 'var(--emerald)';
      case 'Cancelled': return 'var(--rust)';
      case 'Completed': return 'var(--slate)';
      default: return 'var(--gold)';
    }
  };

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
      <div className="week-row">
        <div className="week-hour"></div>
        {days.map((d) => (
          <div key={d.toISOString()} className="text-center text-faint small fw-bold py-2">
            {d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' })}
          </div>
        ))}
        {hours.map((hour) => (
          <React.Fragment key={hour}>
            <div className="week-hour">
              {hour % 12 === 0 ? 12 : hour % 12}{hour >= 12 ? 'PM' : 'AM'}
            </div>
            {days.map((_, idx) => {
              const key = `${idx}-${String(hour).padStart(2, '0')}`;
              const res = grouped[key] || [];
              return (
                <div key={`${idx}-${hour}`} className="week-slot">
                  {res.length > 0 ? (
                    res.map((r) => (
                      <div
                        key={r.id}
                        className={`p-1 mb-1 rounded reservation-block ${getStatusClass(r.status)}`}
                        style={{
                          background: 'var(--surface-2)',
                          fontSize: '.7rem',
                        }}
                      >
                        {r.guest_name} ({r.party_size})
                      </div>
                    ))
                  ) : (
                    <div className="text-muted-soft text-center" style={{ fontSize: '.6rem', opacity: 0.3 }}>
                      —
                    </div>
                  )}
                </div>
              );
            })}
          </React.Fragment>
        ))}
      </div>
    </div>
  );
}