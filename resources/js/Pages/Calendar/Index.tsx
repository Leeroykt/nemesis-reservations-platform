/**
 * Calendar Page – Month & Week views
 * Ref: 02-FEATURE-SPEC.md §4, 08-UI-DESIGN-SYSTEM.md (calendar components)
 */

import React, { useState, useMemo, useCallback } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { formatDate } from '@/lib/timezone';
import MonthView from './MonthView';
import WeekView from './WeekView';

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