import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import KpiCard from '@/Components/Common/KpiCard';
import RevenueChart from '@/Components/Charts/RevenueChart';
import StatusDoughnut from '@/Components/Charts/StatusDoughnut';
import { useApi } from '@/hooks/useApi';
import Toast from '@/Components/Common/Toast';
import { formatDate, formatTime } from '@/lib/timezone';

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

interface TodayReservation {
  id: number;
  guest_name: string;
  time: string;
  party_size: number;
  status: string;
  table: { code: string } | null;
}

export default function Overview() {
  const { restaurant } = usePage<PageProps>().props;
  const timezone = restaurant?.timezone || 'Africa/Harare';
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  // Fetch data
  const { data: kpis, loading: kpisLoading, error: kpisError, refetch: refetchKpis } = useApi<KpiData>('/dashboard/kpis');
  const { data: revenueTrend, loading: revenueLoading, refetch: refetchRevenue } = useApi<RevenueTrendData>('/dashboard/revenue-trend');
  const { data: statusBreakdown, loading: statusLoading, refetch: refetchStatus } = useApi<StatusBreakdownData>('/dashboard/status-breakdown');
  const { data: activity, loading: activityLoading, refetch: refetchActivity } = useApi<ActivityItem[]>('/activity');
  
  // Fetch today's reservations
  const today = new Date().toISOString().split('T')[0];
  const { data: todayReservations, loading: todayLoading } = useApi<{ data: TodayReservation[] }>(
    '/reservations',
    { from: today, to: today, per_page: 10 }
  );

  const isLoading = kpisLoading || revenueLoading || statusLoading || activityLoading || todayLoading;

  const showToast = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  const handleRefresh = () => {
    refetchKpis();
    refetchRevenue();
    refetchStatus();
    refetchActivity();
    showToast('Dashboard refreshed', 'info');
  };

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
          <button className="btn btn-gold mt-3" onClick={handleRefresh}>
            <i className="bi bi-arrow-counterclockwise me-1"></i> Retry
          </button>
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
  const todayData = todayReservations?.data || [];

  const getStatusBadgeClass = (status: string): string => {
    switch (status) {
      case 'Confirmed': return 'confirmed';
      case 'Cancelled': return 'cancelled';
      case 'Completed': return 'completed';
      default: return 'upcoming';
    }
  };

  return (
    <DashboardLayout>
      {/* Header with Refresh */}
      <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h3 className="fw-bold mb-1">Overview</h3>
          <p className="text-muted-soft mb-0 small">Real-time dashboard for your restaurant.</p>
        </div>
        <button className="btn btn-dark-ghost btn-sm" onClick={handleRefresh}>
          <i className="bi bi-arrow-counterclockwise me-1"></i> Refresh
        </button>
      </div>

      {/* KPI Cards */}
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

      {/* Charts */}
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

      {/* Today's Reservations & Activity */}
      <div className="row g-3">
        <div className="col-xl-7">
          <div className="card-elev p-3 p-md-4 h-100">
            <div className="d-flex align-items-center justify-content-between mb-3">
              <h6 className="fw-bold mb-0">Today's reservations</h6>
              <a href="/dashboard/reservations" className="small text-gold">View all <i className="bi bi-arrow-right"></i></a>
            </div>
            {todayData.length === 0 ? (
              <div className="text-muted-soft small py-3 text-center">No reservations for today.</div>
            ) : (
              <div className="table-responsive">
                <table className="table-app" style={{ fontSize: '.85rem' }}>
                  <thead>
                    <tr>
                      <th>Guest</th>
                      <th>Time</th>
                      <th>Party</th>
                      <th>Table</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {todayData.map((res) => (
                      <tr key={res.id}>
                        <td>{res.guest_name}</td>
                        <td>{formatTime(res.time, timezone)}</td>
                        <td>{res.party_size}</td>
                        <td>{res.table?.code || '—'}</td>
                        <td>
                          <span className={`badge-status ${getStatusBadgeClass(res.status)}`}>
                            <span className={`status-dot ${getStatusBadgeClass(res.status)}`}></span>
                            {res.status}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
        <div className="col-xl-5">
          <div className="card-elev p-3 p-md-4 h-100">
            <h6 className="fw-bold mb-3">Recent activity</h6>
            <div id="activityFeed">
              {activityData.length === 0 ? (
                <div className="text-muted-soft small py-3 text-center">No recent activity.</div>
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

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}