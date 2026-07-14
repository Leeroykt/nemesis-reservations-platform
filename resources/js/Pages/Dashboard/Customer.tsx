/**
 * Customers Page – Guest CRM with search, VIP filter, and profile modal
 * Ref: 02-FEATURE-SPEC.md §6, 08-UI-DESIGN-SYSTEM.md (tables, modals)
 */

import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import StatusBadge from '@/Components/Common/StatusBadge';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import { formatDate } from '@/lib/timezone';

// ---------- Types ----------
interface Customer {
  id: number;
  name: string;
  email: string | null;
  phone: string;
  visits: number;
  last_visit_at: string | null;
  is_vip: boolean;
  lifetime_spend: number;
  preferences: { id: number; note: string }[];
  reservations: {
    id: number;
    public_ref: string;
    date: string;
    time: string;
    party_size: number;
    status: string;
    table: { code: string } | null;
  }[];
}

interface Meta {
  total: number;
  page: number;
  perPage: number;
  hasMore: boolean;
}

// ---------- Main Component ----------
export default function Customers() {
  const { restaurant } = usePage<PageProps>().props;
  const timezone = restaurant?.timezone || 'Africa/Harare';

  const [search, setSearch] = useState('');
  const [vipOnly, setVipOnly] = useState(false);
  const [page, setPage] = useState(1);
  const [selectedCustomer, setSelectedCustomer] = useState<Customer | null>(null);
  const [isProfileOpen, setIsProfileOpen] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  // Fetch customers
  const { data, loading, error, refetch } = useApi<{ data: Customer[]; meta: Meta }>(
    '/customers',
    {
      search: search || undefined,
      vip: vipOnly ? '1' : undefined,
      page,
      per_page: 20,
    }
  );

  const customers = data?.data || [];
  const meta = data?.meta || { total: 0, page: 1, perPage: 20, hasMore: false };

  // Toast helper
  const showToast = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  // Open profile modal
  const openProfile = async (customerId: number) => {
    try {
      const response = await api.get<{ data: Customer }>(`/customers/${customerId}`);
      setSelectedCustomer(response.data.data);
      setIsProfileOpen(true);
    } catch (err: any) {
      showToast(err.message || 'Failed to load customer details', 'error');
    }
  };

  const closeProfile = () => {
    setIsProfileOpen(false);
    setSelectedCustomer(null);
  };

  // Toggle VIP (optimistic update)
  const toggleVip = async (customerId: number, currentVip: boolean) => {
    try {
      await api.patch(`/customers/${customerId}`, { is_vip: !currentVip });
      showToast(`VIP ${!currentVip ? 'enabled' : 'disabled'}`);
      refetch();
    } catch (err: any) {
      showToast(err.message || 'Update failed', 'error');
    }
  };

  return (
    <DashboardLayout>
      {/* Header */}
      <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h3 className="fw-bold mb-1">Customers</h3>
          <p className="text-muted-soft mb-0 small">Guest CRM with visit history and preferences.</p>
        </div>
        <div className="d-flex flex-wrap gap-2 align-items-center">
          <button
            className="btn btn-dark-ghost btn-sm"
            onClick={() => refetch()}
            title="Refresh"
          >
            <i className="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>

      {/* Filters */}
      <div className="card-elev p-3 mb-3">
        <div className="d-flex flex-wrap align-items-center gap-3">
          <div className="topbar-search" style={{ flex: '1', maxWidth: '320px' }}>
            <i className="bi bi-search"></i>
            <input
              type="text"
              placeholder="Search by name, email, phone…"
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            />
          </div>
          <div className="form-check">
            <input
              className="form-check-input"
              type="checkbox"
              id="vipFilter"
              checked={vipOnly}
              onChange={() => { setVipOnly(!vipOnly); setPage(1); }}
            />
            <label className="form-check-label small text-muted-soft" htmlFor="vipFilter">
              VIP only
            </label>
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="card-elev">
        {loading ? (
          <div className="p-4 text-center">
            <div className="spinner-border" role="status"><span className="visually-hidden">Loading...</span></div>
          </div>
        ) : error ? (
          <div className="p-4 text-center text-rust">{error}</div>
        ) : customers.length === 0 ? (
          <div className="p-4 text-center text-muted-soft">No customers found.</div>
        ) : (
          <>
            <div className="table-responsive">
              <table className="table-app">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Visits</th>
                    <th>Last visit</th>
                    <th>Spend</th>
                    <th>VIP</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  {customers.map((customer) => (
                    <tr key={customer.id} onClick={() => openProfile(customer.id)} style={{ cursor: 'pointer' }}>
                      <td>
                        <div className="d-flex align-items-center gap-2">
                          <span className="avatar-sm">
                            {customer.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()}
                          </span>
                          {customer.name}
                        </div>
                      </td>
                      <td>{customer.email || '—'}</td>
                      <td>{customer.phone}</td>
                      <td>{customer.visits}</td>
                      <td>{customer.last_visit_at ? formatDate(customer.last_visit_at, timezone) : '—'}</td>
                      <td>${customer.lifetime_spend.toFixed(2)}</td>
                      <td>
                        {customer.is_vip ? (
                          <span className="badge-vip">VIP</span>
                        ) : (
                          <span className="text-muted-soft" style={{ fontSize: '.75rem' }}>—</span>
                        )}
                      </td>
                      <td className="text-end" onClick={(e) => e.stopPropagation()}>
                        <div className="dropdown">
                          <button
                            className="icon-btn"
                            data-bs-toggle="dropdown"
                          >
                            <i className="bi bi-three-dots-vertical"></i>
                          </button>
                          <ul className="dropdown-menu dropdown-menu-end shadow">
                            <li>
                              <button
                                className="dropdown-item"
                                onClick={() => toggleVip(customer.id, customer.is_vip)}
                              >
                                {customer.is_vip ? 'Remove VIP' : 'Make VIP'}
                              </button>
                            </li>
                            <li>
                              <button
                                className="dropdown-item"
                                onClick={() => openProfile(customer.id)}
                              >
                                View profile
                              </button>
                            </li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            {/* Pagination */}
            <div className="d-flex align-items-center justify-content-between p-3 border-top" style={{ borderColor: 'var(--border) !important' }}>
              <span className="text-muted-soft small">
                Showing {customers.length} of {meta.total} customers
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

      {/* Profile Modal */}
      {isProfileOpen && selectedCustomer && (
        <CustomerProfile
          customer={selectedCustomer}
          onClose={closeProfile}
          timezone={timezone}
          onVipToggle={toggleVip}
        />
      )}

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}

// ---------- Customer Profile Modal ----------
interface CustomerProfileProps {
  customer: Customer;
  onClose: () => void;
  timezone: string;
  onVipToggle: (id: number, currentVip: boolean) => void;
}

function CustomerProfile({ customer, onClose, timezone, onVipToggle }: CustomerProfileProps) {
  const [isToggling, setIsToggling] = useState(false);

  const handleVipToggle = async () => {
    setIsToggling(true);
    await onVipToggle(customer.id, customer.is_vip);
    setIsToggling(false);
  };

  return (
    <div
      className="modal fade show d-block"
      tabIndex={-1}
      style={{ display: 'block', background: 'rgba(0,0,0,0.5)' }}
      onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
    >
      <div className="modal-dialog modal-dialog-centered modal-lg">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title fw-bold">
              {customer.name}
              {customer.is_vip && <span className="badge-vip ms-2">VIP</span>}
            </h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <div className="modal-body">
            <div className="row mb-3">
              <div className="col-md-6">
                <div className="text-faint small">Email</div>
                <div>{customer.email || '—'}</div>
              </div>
              <div className="col-md-6">
                <div className="text-faint small">Phone</div>
                <div>{customer.phone}</div>
              </div>
              <div className="col-md-4">
                <div className="text-faint small">Visits</div>
                <div className="fw-semibold">{customer.visits}</div>
              </div>
              <div className="col-md-4">
                <div className="text-faint small">Last visit</div>
                <div>{customer.last_visit_at ? formatDate(customer.last_visit_at, timezone) : '—'}</div>
              </div>
              <div className="col-md-4">
                <div className="text-faint small">Lifetime spend</div>
                <div className="fw-semibold">${customer.lifetime_spend.toFixed(2)}</div>
              </div>
            </div>

            {/* Preferences */}
            <div className="mb-3">
              <div className="text-faint small">Preferences</div>
              {customer.preferences.length === 0 ? (
                <span className="text-muted-soft small">No preferences recorded.</span>
              ) : (
                <ul className="list-unstyled mb-0">
                  {customer.preferences.map((p) => (
                    <li key={p.id} className="small">• {p.note}</li>
                  ))}
                </ul>
              )}
            </div>

            {/* Reservations History */}
            <div>
              <div className="text-faint small mb-2">Reservation history</div>
              {customer.reservations.length === 0 ? (
                <span className="text-muted-soft small">No reservations yet.</span>
              ) : (
                <div className="table-responsive">
                  <table className="table-app" style={{ fontSize: '.85rem' }}>
                    <thead>
                      <tr>
                        <th>Ref</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Party</th>
                        <th>Table</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {customer.reservations.map((r) => (
                        <tr key={r.id}>
                          <td>{r.public_ref}</td>
                          <td>{formatDate(r.date, timezone)}</td>
                          <td>{r.time}</td>
                          <td>{r.party_size}</td>
                          <td>{r.table?.code || '—'}</td>
                          <td><StatusBadge status={r.status} /></td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
          <div className="modal-footer">
            <LoadingButton
              isLoading={isToggling}
              className={`btn btn-sm ${customer.is_vip ? 'btn-outline-danger' : 'btn-gold'}`}
              onClick={handleVipToggle}
            >
              {customer.is_vip ? 'Remove VIP' : 'Make VIP'}
            </LoadingButton>
            <button className="btn btn-dark-ghost btn-sm" onClick={onClose}>Close</button>
          </div>
        </div>
      </div>
    </div>
  );
}