import React, { useState } from 'react';
import StatusBadge from '@/Components/Common/StatusBadge';
import LoadingButton from '@/Components/Common/LoadingButton';
import { formatDate } from '@/lib/timezone';

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

interface CustomerProfileProps {
  customer: Customer;
  onClose: () => void;
  timezone: string;
  onVipToggle: (id: number, currentVip: boolean) => void;
}

export default function CustomerProfile({ customer, onClose, timezone, onVipToggle }: CustomerProfileProps) {
  const [isToggling, setIsToggling] = useState(false);

  const handleVipToggle = async () => {
    setIsToggling(true);
    await onVipToggle(customer.id, customer.is_vip);
    setIsToggling(false);
  };

  const getInitials = (name: string): string => {
    const parts = name.split(' ');
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
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
            {/* Customer Info */}
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