import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import Toast from '@/Components/Common/Toast';

interface AuditEntry {
  id: number;
  actor_label: string;
  actor_user_id: number | null;
  icon: string;
  tone: 'gold' | 'emerald' | 'rust' | 'slate';
  description: string;
  entity_type: string | null;
  entity_id: number | null;
  created_at: string;
  actor?: {
    id: number;
    name: string;
  };
}

interface Meta {
  total: number;
  page: number;
  perPage: number;
  hasMore: boolean;
}

const toneColors = {
  gold: 'var(--gold)',
  emerald: 'var(--emerald)',
  rust: 'var(--rust)',
  slate: 'var(--slate)',
};

export default function AuditLog() {
  const [filters, setFilters] = useState({
    search: '',
    from: '',
    to: '',
    actor: '',
    entity_type: '',
    tone: '',
  });
  const [page, setPage] = useState(1);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  // Fetch audit logs
  const { data, loading, error, refetch } = useApi<{ data: AuditEntry[]; meta: Meta }>(
    '/audit-log',
    {
      search: filters.search || undefined,
      from: filters.from || undefined,
      to: filters.to || undefined,
      actor: filters.actor || undefined,
      entity_type: filters.entity_type || undefined,
      tone: filters.tone || undefined,
      page,
      per_page: 25,
    }
  );

  // Fetch entity types for filter dropdown
  const { data: entityTypes } = useApi<string[]>('/audit-log/entity-types');

  const logs = data?.data || [];
  const meta = data?.meta || { total: 0, page: 1, perPage: 25, hasMore: false };

  const showToast = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  const handleFilterChange = (key: string, value: string) => {
    setFilters(prev => ({ ...prev, [key]: value }));
    setPage(1);
  };

  const clearFilters = () => {
    setFilters({ search: '', from: '', to: '', actor: '', entity_type: '', tone: '' });
    setPage(1);
    showToast('Filters cleared', 'info');
  };

  const handleRefresh = () => {
    refetch();
    showToast('Refreshed audit log', 'info');
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getInitials = (name?: string): string => {
    if (!name) return 'S';
    const parts = name.split(' ');
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
  };

  return (
    <DashboardLayout>
      <div className="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
          <h3 className="fw-bold mb-1">Audit Log</h3>
          <p className="text-muted-soft mb-0 small">
            Complete history of all actions performed in your restaurant.
            {meta.total > 0 && ` ${meta.total.toLocaleString()} entries found.`}
          </p>
        </div>
        <button className="btn btn-dark-ghost btn-sm" onClick={handleRefresh}>
          <i className="bi bi-arrow-counterclockwise me-1"></i> Refresh
        </button>
      </div>

      {/* Filters */}
      <div className="card-elev p-3 mb-3">
        <div className="row g-2 align-items-end">
          <div className="col-md-3">
            <label className="form-label small">Search</label>
            <div className="topbar-search" style={{ maxWidth: '100%' }}>
              <i className="bi bi-search"></i>
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Search description..."
                value={filters.search}
                onChange={(e) => handleFilterChange('search', e.target.value)}
              />
            </div>
          </div>
          <div className="col-md-2">
            <label className="form-label small">From</label>
            <input
              type="date"
              className="form-control form-control-sm"
              value={filters.from}
              onChange={(e) => handleFilterChange('from', e.target.value)}
            />
          </div>
          <div className="col-md-2">
            <label className="form-label small">To</label>
            <input
              type="date"
              className="form-control form-control-sm"
              value={filters.to}
              onChange={(e) => handleFilterChange('to', e.target.value)}
            />
          </div>
          <div className="col-md-2">
            <label className="form-label small">Actor</label>
            <input
              type="text"
              className="form-control form-control-sm"
              placeholder="Staff name..."
              value={filters.actor}
              onChange={(e) => handleFilterChange('actor', e.target.value)}
            />
          </div>
          <div className="col-md-2">
            <label className="form-label small">Entity</label>
            <select
              className="form-select form-select-sm"
              value={filters.entity_type}
              onChange={(e) => handleFilterChange('entity_type', e.target.value)}
            >
              <option value="">All</option>
              {entityTypes?.map((type) => (
                <option key={type} value={type}>{type}</option>
              ))}
            </select>
          </div>
          <div className="col-md-1">
            <button
              className="btn btn-dark-ghost btn-sm w-100"
              onClick={clearFilters}
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="card-elev">
        {loading ? (
          <div className="p-4 text-center">
            <div className="spinner-border" role="status">
              <span className="visually-hidden">Loading...</span>
            </div>
          </div>
        ) : error ? (
          <div className="p-4 text-center text-rust">{error}</div>
        ) : logs.length === 0 ? (
          <div className="p-4 text-center text-muted-soft">
            <i className="bi bi-inbox" style={{ fontSize: '2rem', display: 'block' }}></i>
            <p className="mt-2">No audit entries found.</p>
            <p className="small text-muted-soft">Try adjusting your filters.</p>
          </div>
        ) : (
          <>
            <div className="table-responsive">
              <table className="table-app">
                <thead>
                  <tr>
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  {logs.map((log) => (
                    <tr key={log.id}>
                      <td className="text-muted-soft small" style={{ whiteSpace: 'nowrap' }}>
                        {formatDate(log.created_at)}
                      </td>
                      <td>
                        <div className="d-flex align-items-center gap-2">
                          {log.actor_user_id ? (
                            <>
                              <span className="avatar-sm">
                                {getInitials(log.actor?.name)}
                              </span>
                              <span>{log.actor_label}</span>
                            </>
                          ) : (
                            <span className="text-muted-soft">{log.actor_label}</span>
                          )}
                        </div>
                      </td>
                      <td>
                        <div className="d-flex align-items-center gap-2">
                          <i 
                            className={`bi ${log.icon}`}
                            style={{ color: toneColors[log.tone] }}
                          ></i>
                          <span>{log.description}</span>
                        </div>
                      </td>
                      <td>
                        {log.entity_type ? (
                          <span className="badge bg-dark-ghost" style={{ 
                            fontSize: '0.7rem',
                            padding: '2px 10px',
                            borderRadius: '12px',
                            background: 'var(--surface-2)',
                          }}>
                            {log.entity_type}
                          </span>
                        ) : (
                          <span className="text-muted-soft small">—</span>
                        )}
                      </td>
                      <td>
                        <span className={`status-dot ${log.tone}`}></span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            <div className="d-flex align-items-center justify-content-between p-3 border-top" style={{ borderColor: 'var(--border) !important' }}>
              <span className="text-muted-soft small">
                Showing {logs.length} of {meta.total.toLocaleString()} entries
              </span>
              <div className="d-flex gap-2">
                <button
                  className="btn btn-dark-ghost btn-sm"
                  disabled={meta.page <= 1}
                  onClick={() => setPage(meta.page - 1)}
                >
                  Previous
                </button>
                <span className="d-flex align-items-center small text-muted-soft">
                  Page {meta.page} of {Math.ceil(meta.total / meta.perPage)}
                </span>
                <button
                  className="btn btn-dark-ghost btn-sm"
                  disabled={!meta.hasMore}
                  onClick={() => setPage(meta.page + 1)}
                >
                  Next
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}