import React from 'react';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import KpiCard from '@/Components/Common/KpiCard';
import RevenueChart from '@/Components/Charts/RevenueChart';
import StatusDoughnut from '@/Components/Charts/StatusDoughnut';
import { useApi } from '@/hooks/useApi';

interface KpiData {
  todayReservations: number;
  todayReservationsDelta: number;
  upcomingReservations: number;
  upcomingReservationsDelta: number;
  tablesAvailable: number;
  tablesOccupied: number;
  walkIns: number;
  walkInsDelta: number;
  revenueToday: number;
  revenueDelta: number;
  avgPartySize: number;
  noShowRate: number;
}

interface RevenueTrendData {
  labels: string[];
  thisWeek: number[];
  lastWeek: number[];
}

interface StatusBreakdownData {
  labels: string[];
  values: number[];
}

interface ActivityItem {
  id: number;
  icon: string;
  tone: 'gold' | 'emerald' | 'rust' | 'slate';
  text: string;
  time: string;
}

export default function Overview() {
  const { data: kpis, loading: kpisLoading, error: kpisError } = useApi<KpiData>('/dashboard/kpis');
  const { data: revenueTrend, loading: revenueLoading } = useApi<RevenueTrendData>('/dashboard/revenue-trend');
  const { data: statusBreakdown, loading: statusLoading } = useApi<StatusBreakdownData>('/dashboard/status-breakdown');
  const { data: activity, loading: activityLoading } = useApi<ActivityItem[]>('/activity');

  const isLoading = kpisLoading || revenueLoading || statusLoading || activityLoading;

  if (isLoading) {
    return (
      <DashboardLayout>
        <div className="d-flex justify-content-center align-items-center" style={{ height: '400px' }}>
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  if (kpisError) {
    return (
      <DashboardLayout>
        <div className="text-center py-5">
          <i className="bi bi-exclamation-triangle text-rust" style={{ fontSize: '2rem' }}></i>
          <h5 className="mt-3 fw-bold">Failed to load dashboard</h5>
          <p className="text-muted-soft">{kpisError}</p>
        </div>
      </DashboardLayout>
    );
  }

  const kpiData = kpis || {
    todayReservations: 0,
    todayReservationsDelta: 0,
    upcomingReservations: 0,
    upcomingReservationsDelta: 0,
    tablesAvailable: 0,
    tablesOccupied: 0,
    walkIns: 0,
    walkInsDelta: 0,
    revenueToday: 0,
    revenueDelta: 0,
    avgPartySize: 0,
    noShowRate: 0,
  };

  const kpiItems = [
    { label: "Today's reservations", value: kpiData.todayReservations, delta: kpiData.todayReservationsDelta, icon: 'bi-calendar-check', tone: 'gold' as const },
    { label: 'Upcoming reservations', value: kpiData.upcomingReservations, delta: kpiData.upcomingReservationsDelta, icon: 'bi-calendar-week', tone: 'emerald' as const },
    { label: 'Available tables', value: kpiData.tablesAvailable, delta: null, icon: 'bi-door-open', tone: 'emerald' as const },
    { label: 'Occupied tables', value: kpiData.tablesOccupied, delta: null, icon: 'bi-door-closed', tone: 'rust' as const },
    { label: 'Walk-ins today', value: kpiData.walkIns, delta: kpiData.walkInsDelta, icon: 'bi-person-walking', tone: 'slate' as const },
    { label: "Today's revenue", value: `$${kpiData.revenueToday.toLocaleString()}`, delta: kpiData.revenueDelta, icon: 'bi-currency-dollar', tone: 'gold' as const },
  ];

  const revenueData = revenueTrend || { labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], thisWeek: [], lastWeek: [] };
  const statusData = statusBreakdown || { labels: ['Confirmed', 'Upcoming', 'Completed', 'Cancelled'], values: [] };
  const activityData = activity || [];

  return (
    <DashboardLayout>
      <div className="row g-3 mb-4">
        {kpiItems.map((item, index) => (
          <div key={index} className="col-6 col-lg-4 col-xl-2">
            <KpiCard
              label={item.label}
              value={item.value}
              icon={item.icon}
              delta={item.delta}
              tone={item.tone}
            />
          </div>
        ))}
      </div>

      <div className="row g-3 mb-3">
        <div className="col-xl-8">
          <div className="card-elev p-3 p-md-4 h-100">
            <div className="d-flex align-items-center justify-content-between mb-3">
              <h6 className="fw-bold mb-0">Revenue trend</h6>
              <span className="badge-status confirmed"><span className="status-dot confirmed"></span>Live</span>
            </div>
            <RevenueChart
              labels={revenueData.labels}
              thisWeek={revenueData.thisWeek}
              lastWeek={revenueData.lastWeek}
            />
          </div>
        </div>
        <div className="col-xl-4">
          <div className="card-elev p-3 p-md-4 h-100">
            <h6 className="fw-bold mb-3">Reservations by status</h6>
            <StatusDoughnut labels={statusData.labels} values={statusData.values} />
          </div>
        </div>
      </div>

      <div className="row g-3">
        <div className="col-xl-7">
          <div className="card-elev p-3 p-md-4 h-100">
            <div className="d-flex align-items-center justify-content-between mb-3">
              <h6 className="fw-bold mb-0">Today's reservations</h6>
              <a href="/dashboard/reservations" className="small text-gold">View all <i className="bi bi-arrow-right"></i></a>
            </div>
            <div id="todayResList">
              <div className="text-muted-soft small py-3">No reservations for today.</div>
            </div>
          </div>
        </div>
        <div className="col-xl-5">
          <div className="card-elev p-3 p-md-4 h-100">
            <h6 className="fw-bold mb-3">Recent activity</h6>
            <div id="activityFeed">
              {activityData.length === 0 ? (
                <div className="text-muted-soft small py-3">No recent activity.</div>
              ) : (
                activityData.slice(0, 12).map((item) => (
                  <div key={item.id} className="d-flex gap-3 py-2 border-bottom" style={{ borderColor: 'var(--border) !important' }}>
                    <span
                      className="kpi-icon"
                      style={{
                        width: '34px',
                        height: '34px',
                        fontSize: '.85rem',
                        background: `rgba(${item.tone === 'gold' ? '201,162,39' : item.tone === 'emerald' ? '63,166,114' : item.tone === 'rust' ? '193,80,61' : '108,122,137'}, .14)`,
                        color: `var(--${item.tone})`,
                      }}
                    >
                      <i className={`bi ${item.icon}`}></i>
                    </span>
                    <div className="flex-grow-1">
                      <div className="small">{item.text}</div>
                      <div className="text-faint" style={{ fontSize: '.72rem' }}>{item.time}</div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
}