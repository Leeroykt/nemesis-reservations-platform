/**
 * Analytics Page – Peak hours, popular tables, customer growth charts
 * Ref: 02-FEATURE-SPEC.md §7, 08-UI-DESIGN-SYSTEM.md (charts)
 */

import React, { useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import Toast from '@/Components/Common/Toast';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { Bar, Line } from 'react-chartjs-2';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

// ---------- Types ----------
interface PeakHoursData {
  labels: string[];
  covers: number[];
}

interface PopularTablesData {
  labels: string[];
  bookings: number[];
}

interface CustomerGrowthData {
  labels: string[];
  newCustomers: number[];
  returning: number[];
}

// ---------- Main Component ----------
export default function Analytics() {
  const { restaurant } = usePage<PageProps>().props;
  const timezone = restaurant?.timezone || 'Africa/Harare';
  const [toast, setToast] = React.useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  // Fetch data for each chart
  const {
    data: peakData,
    loading: peakLoading,
    error: peakError,
    refetch: refetchPeak
  } = useApi<PeakHoursData>('/analytics/peak-hours');

  const {
    data: popularData,
    loading: popularLoading,
    error: popularError,
    refetch: refetchPopular
  } = useApi<PopularTablesData>('/analytics/popular-tables');

  const {
    data: growthData,
    loading: growthLoading,
    error: growthError,
    refetch: refetchGrowth
  } = useApi<CustomerGrowthData>('/analytics/customer-growth');

  const isLoading = peakLoading || popularLoading || growthLoading;
  const hasError = peakError || popularError || growthError;

  // Toast helper
  const showToast = (message: string, type: 'success' | 'error' | 'info' = 'error') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  // Refresh all charts
  const refreshAll = () => {
    refetchPeak();
    refetchPopular();
    refetchGrowth();
  };

  // Chart options – fixed font weights (numeric)
  const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        labels: {
          color: 'var(--text-muted)',
          font: {
            family: 'Raleway',
            size: 11,
            weight: 700,
          },
          usePointStyle: true,
          pointStyle: 'circle',
        },
      },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: {
          color: 'var(--text-muted)',
          font: {
            family: 'Raleway',
            size: 11,
            weight: 700,
          },
        },
      },
      y: {
        grid: { color: 'var(--border)' },
        ticks: {
          color: 'var(--text-muted)',
          font: {
            family: 'Raleway',
            size: 11,
            weight: 700,
          },
          stepSize: 1,
        },
        beginAtZero: true,
      },
    },
  };

  const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        labels: {
          color: 'var(--text-muted)',
          font: {
            family: 'Raleway',
            size: 11,
            weight: 700,
          },
          usePointStyle: true,
          pointStyle: 'circle',
        },
      },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: {
          color: 'var(--text-muted)',
          font: {
            family: 'Raleway',
            size: 11,
            weight: 700,
          },
        },
      },
      y: {
        grid: { color: 'var(--border)' },
        ticks: {
          color: 'var(--text-muted)',
          font: {
            family: 'Raleway',
            size: 11,
            weight: 700,
          },
          stepSize: 1,
        },
        beginAtZero: true,
      },
    },
  };

  // Prepare chart data
  const peakChartData = useMemo(() => {
    const labels = peakData?.labels || [];
    const values = peakData?.covers || [];
    return {
      labels,
      datasets: [
        {
          label: 'Reservations',
          data: values,
          backgroundColor: 'rgba(201,162,39,0.6)',
          borderColor: 'var(--gold)',
          borderWidth: 2,
          borderRadius: 4,
        },
      ],
    };
  }, [peakData]);

  const popularChartData = useMemo(() => {
    const labels = popularData?.labels || [];
    const values = popularData?.bookings || [];
    return {
      labels,
      datasets: [
        {
          label: 'Bookings',
          data: values,
          backgroundColor: 'rgba(63,166,114,0.6)',
          borderColor: 'var(--emerald)',
          borderWidth: 2,
          borderRadius: 4,
        },
      ],
    };
  }, [popularData]);

  const growthChartData = useMemo(() => {
    const labels = growthData?.labels || [];
    const newCustomers = growthData?.newCustomers || [];
    const returning = growthData?.returning || [];
    return {
      labels,
      datasets: [
        {
          label: 'New customers',
          data: newCustomers,
          borderColor: 'var(--gold)',
          backgroundColor: 'rgba(201,162,39,0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          pointBackgroundColor: 'var(--gold)',
        },
        {
          label: 'Returning',
          data: returning,
          borderColor: 'var(--emerald)',
          backgroundColor: 'rgba(63,166,114,0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 3,
          pointBackgroundColor: 'var(--emerald)',
        },
      ],
    };
  }, [growthData]);

  // Check if any chart has empty data
  const hasData =
    (peakData?.labels?.length || 0) > 0 ||
    (popularData?.labels?.length || 0) > 0 ||
    (growthData?.labels?.length || 0) > 0;

  return (
    <DashboardLayout>
      {/* Header */}
      <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h3 className="fw-bold mb-1">Analytics</h3>
          <p className="text-muted-soft mb-0 small">Insights into reservations, tables, and customer behaviour.</p>
        </div>
        <button
          className="btn btn-dark-ghost btn-sm"
          onClick={refreshAll}
          title="Refresh all data"
        >
          <i className="bi bi-arrow-counterclockwise me-1"></i>Refresh
        </button>
      </div>

      {isLoading ? (
        <div className="text-center py-5">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      ) : hasError ? (
        <div className="text-center text-rust py-5">
          <i className="bi bi-exclamation-triangle me-2"></i>
          {peakError || popularError || growthError || 'Failed to load analytics data'}
        </div>
      ) : !hasData ? (
        <div className="card-elev p-5 text-center text-muted-soft">
          <i className="bi bi-bar-chart-line" style={{ fontSize: '2rem' }}></i>
          <h5 className="mt-3 fw-bold">No data yet</h5>
          <p className="small">Start taking reservations to see analytics.</p>
        </div>
      ) : (
        <div className="row g-3">
          {/* Peak Hours */}
          <div className="col-xl-6">
            <div className="card-elev p-3 p-md-4 h-100">
              <h6 className="fw-bold mb-3">Peak hours</h6>
              <div style={{ height: '240px' }}>
                {peakData?.labels?.length ? (
                  <Bar data={peakChartData} options={barOptions} />
                ) : (
                  <div className="text-center text-muted-soft small py-4">No data</div>
                )}
              </div>
            </div>
          </div>

          {/* Popular Tables */}
          <div className="col-xl-6">
            <div className="card-elev p-3 p-md-4 h-100">
              <h6 className="fw-bold mb-3">Popular tables</h6>
              <div style={{ height: '240px' }}>
                {popularData?.labels?.length ? (
                  <Bar data={popularChartData} options={barOptions} />
                ) : (
                  <div className="text-center text-muted-soft small py-4">No data</div>
                )}
              </div>
            </div>
          </div>

          {/* Customer Growth */}
          <div className="col-12">
            <div className="card-elev p-3 p-md-4">
              <h6 className="fw-bold mb-3">Customer growth</h6>
              <div style={{ height: '240px' }}>
                {growthData?.labels?.length ? (
                  <Line data={growthChartData} options={lineOptions} />
                ) : (
                  <div className="text-center text-muted-soft small py-4">No data</div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}