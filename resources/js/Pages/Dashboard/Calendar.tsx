/**
 * Calendar Page – Month & Week views
 * Ref: 02-FEATURE-SPEC.md §4, 08-UI-DESIGN-SYSTEM.md (calendar components)
 */

import React, { useState, useMemo, useCallback } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { formatDate, formatTime } from '@/lib/timezone';

// ---------- Types ----------
interface Reservation {
  id: number;
  public_ref: string;
  guest_name: string;
  date: string;
  time: string;
  party_size: number;
  status: 'Upcoming' | 'Confirmed' | 'Completed' | 'Cancelled';
  table: { code: string } | null;
}

type ViewMode = 'month' | 'week';

// ---------- Main Component ----------
export default function Calendar() {
  const { restaurant } = usePage<PageProps>().props;
  const timezone = restaurant?.timezone || 'Africa/Harare';

  const [viewMode, setViewMode] = useState<ViewMode>('month');
  const [currentDate, setCurrentDate] = useState(new Date());

  // Compute date range based on current view
  const { from, to } = useMemo(() => {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    let start: Date, end: Date;
    if (viewMode === 'month') {
      start = new Date(year, month, 1);
      end = new Date(year, month + 1, 0);
    } else {
      // Week: Monday to Sunday
      const dayOfWeek = currentDate.getDay(); // 0=Sun
      const diff = currentDate.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
      start = new Date(year, month, diff);
      end = new Date(start);
      end.setDate(start.getDate() + 6);
    }
    return {
      from: start.toISOString().split('T')[0],
      to: end.toISOString().split('T')[0],
    };
  }, [currentDate, viewMode]);

  // Fetch reservations for the range
  const { data, loading, error, refetch } = useApi<{ data: Reservation[] }>(
    '/reservations',
    { from, to, per_page: 100 }
  );

  const reservations = data?.data || [];

  // Group by date (for month view)
  const groupedByDate = useMemo(() => {
    const map: Record<string, Reservation[]> = {};
    reservations.forEach((r) => {
      if (!map[r.date]) map[r.date] = [];
      map[r.date].push(r);
    });
    return map;
  }, [reservations]);

  // Navigation handlers
  const goToPrev = useCallback(() => {
    const newDate = new Date(currentDate);
    if (viewMode === 'month') {
      newDate.setMonth(newDate.getMonth() - 1);
    } else {
      newDate.setDate(newDate.getDate() - 7);
    }
    setCurrentDate(newDate);
  }, [currentDate, viewMode]);

  const goToNext = useCallback(() => {
    const newDate = new Date(currentDate);
    if (viewMode === 'month') {
      newDate.setMonth(newDate.getMonth() + 1);
    } else {
      newDate.setDate(newDate.getDate() + 7);
    }
    setCurrentDate(newDate);
  }, [currentDate, viewMode]);

  const goToToday = useCallback(() => setCurrentDate(new Date()), []);

  // Title string
  const title =
    viewMode === 'month'
      ? currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
      : `Week of ${formatDate(from, timezone)}`;

  return (
    <DashboardLayout>
      {/* Header */}
      <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h3 className="fw-bold mb-1">Calendar</h3>
          <p className="text-muted-soft mb-0 small">{title}</p>
        </div>
        <div className="d-flex flex-wrap gap-2 align-items-center">
          <button className="btn btn-dark-ghost btn-sm" onClick={goToToday}>
            Today
          </button>
          <button className="btn btn-dark-ghost btn-sm" onClick={goToPrev}>
            <i className="bi bi-chevron-left"></i>
          </button>
          <button className="btn btn-dark-ghost btn-sm" onClick={goToNext}>
            <i className="bi bi-chevron-right"></i>
          </button>
          <div className="btn-group" role="group">
            <button
              className={`btn btn-sm ${viewMode === 'month' ? 'btn-gold' : 'btn-dark-ghost'}`}
              onClick={() => setViewMode('month')}
            >
              Month
            </button>
            <button
              className={`btn btn-sm ${viewMode === 'week' ? 'btn-gold' : 'btn-dark-ghost'}`}
              onClick={() => setViewMode('week')}
            >
              Week
            </button>
          </div>
          <button
            className="btn btn-dark-ghost btn-sm"
            onClick={() => refetch()}
            title="Refresh"
          >
            <i className="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>

      {/* Content */}
      {loading ? (
        <div className="text-center py-5">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      ) : error ? (
        <div className="text-center text-rust py-5">
          <i className="bi bi-exclamation-triangle me-2"></i>
          {error}
        </div>
      ) : viewMode === 'month' ? (
        <MonthView
          year={currentDate.getFullYear()}
          month={currentDate.getMonth()}
          grouped={groupedByDate}
          timezone={timezone}
        />
      ) : (
        <WeekView
          startDate={from}
          reservations={reservations}
          timezone={timezone}
        />
      )}
    </DashboardLayout>
  );
}

// ---------- MonthView Subcomponent ----------
interface MonthViewProps {
  year: number;
  month: number;
  grouped: Record<string, Reservation[]>;
  timezone: string;
}

function MonthView({ year, month, grouped, timezone }: MonthViewProps) {
  const firstDay = new Date(year, month, 1).getDay(); // 0=Sun
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
                  className="cal-chip"
                  style={{
                    background: 'var(--surface-2)',
                    padding: '2px 6px',
                    borderRadius: '4px',
                    fontSize: '.7rem',
                    marginTop: '4px',
                    borderLeft: `3px solid var(--${res.status === 'Confirmed' ? 'emerald' : res.status === 'Cancelled' ? 'rust' : 'gold'})`,
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

// ---------- WeekView Subcomponent ----------
interface WeekViewProps {
  startDate: string;
  reservations: Reservation[];
  timezone: string;
}

function WeekView({ startDate, reservations, timezone }: WeekViewProps) {
  const start = new Date(startDate);
  const days: Date[] = [];
  for (let i = 0; i < 7; i++) {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    days.push(d);
  }

  const hours = Array.from({ length: 15 }, (_, i) => i + 8); // 8 AM to 10 PM

  // Group reservations by day index and hour
  const grouped: Record<string, Reservation[]> = {};
  reservations.forEach((r) => {
    const idx = days.findIndex((d) => d.toISOString().split('T')[0] === r.date);
    if (idx === -1) return;
    const hourKey = r.time.split(':')[0];
    const key = `${idx}-${hourKey}`;
    if (!grouped[key]) grouped[key] = [];
    grouped[key].push(r);
  });

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
                  {res.map((r) => (
                    <div
                      key={r.id}
                      className="p-1 mb-1 rounded"
                      style={{
                        background: 'var(--surface-2)',
                        fontSize: '.7rem',
                        borderLeft: `3px solid var(--${r.status === 'Confirmed' ? 'emerald' : r.status === 'Cancelled' ? 'rust' : 'gold'})`,
                      }}
                    >
                      {r.guest_name} ({r.party_size})
                    </div>
                  ))}
                </div>
              );
            })}
          </React.Fragment>
        ))}
      </div>
    </div>
  );
}