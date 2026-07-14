import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import StatusBadge from '@/Components/Common/StatusBadge';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import { formatDate, formatTime } from '@/lib/timezone';

// ---------- Types ----------
interface Reservation {
  id: number;
  public_ref: string;
  guest_name: string;
  guest_phone: string;
  guest_email: string | null;
  date: string;
  time: string;
  party_size: number;
  status: 'Upcoming' | 'Confirmed' | 'Completed' | 'Cancelled';
  notes: string | null;
  source: 'Website' | 'Phone' | 'App' | 'Walk-in';
  table: { id: number; code: string; zone: string } | null;
  created_by: { id: number; name: string } | null;
  created_at: string;
  updated_at: string;
}

interface Meta {
  total: number;
  page: number;
  perPage: number;
  hasMore: boolean;
}

interface ReservationForm {
  guest_name: string;
  guest_phone: string;
  guest_email: string;
  date: string;
  time: string;
  party_size: number;
  table_id: number | null;
  source: string;
  notes: string;
}

const defaultForm: ReservationForm = {
  guest_name: '',
  guest_phone: '',
  guest_email: '',
  date: new Date().toISOString().split('T')[0],
  time: '19:00',
  party_size: 2,
  table_id: null,
  source: 'Website',
  notes: '',
};

// ---------- Main Component ----------
export default function Reservations() {
  const { restaurant } = usePage<PageProps>().props;
  const timezone = restaurant?.timezone || 'Africa/Harare';

  // Filters and pagination
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [selectedIds, setSelectedIds] = useState<number[]>([]);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  // Modal states
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [editingReservation, setEditingReservation] = useState<Reservation | null>(null);

  // Form state
  const [form, setForm] = useState<ReservationForm>(defaultForm);
  const [formErrors, setFormErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  // API fetch
  const { data, loading, error, refetch } = useApi<{ data: Reservation[]; meta: Meta }>(
    '/reservations',
    { status: statusFilter !== 'all' ? statusFilter : undefined, search, page, per_page: 15 }
  );

  const reservations = data?.data || [];
  const meta = data?.meta || { total: 0, page: 1, perPage: 15, hasMore: false };

  // Toast helper
  const showToast = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  // Bulk actions
  const toggleSelectAll = (checked: boolean) => {
    if (checked) {
      setSelectedIds(reservations.map(r => r.id));
    } else {
      setSelectedIds([]);
    }
  };

  const toggleSelect = (id: number) => {
    setSelectedIds(prev =>
      prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
    );
  };

  const handleBulkAction = async (action: 'confirm' | 'cancel' | 'delete') => {
    if (selectedIds.length === 0) return;
    if (action === 'delete' && !confirm('Delete selected reservations?')) return;

    try {
      await api.post('/reservations/bulk', { action, ids: selectedIds });
      showToast(`${action}ed ${selectedIds.length} reservation(s)`);
      setSelectedIds([]);
      refetch();
    } catch (err: any) {
      showToast(err.message || 'Action failed', 'error');
    }
  };

  // Open edit modal
  const openEditModal = (reservation: Reservation) => {
    setEditingReservation(reservation);
    setForm({
      guest_name: reservation.guest_name,
      guest_phone: reservation.guest_phone,
      guest_email: reservation.guest_email || '',
      date: reservation.date,
      time: reservation.time,
      party_size: reservation.party_size,
      table_id: reservation.table?.id || null,
      source: reservation.source,
      notes: reservation.notes || '',
    });
    setFormErrors({});
    setIsEditModalOpen(true);
  };

  const closeModals = () => {
    setIsCreateModalOpen(false);
    setIsEditModalOpen(false);
    setEditingReservation(null);
    setForm(defaultForm);
    setFormErrors({});
    setIsSubmitting(false);
  };

  // Create
  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setFormErrors({});

    try {
      await api.post('/reservations', form);
      showToast('Reservation created!');
      closeModals();
      refetch();
    } catch (err: any) {
      if (err.errors) {
        setFormErrors(err.errors);
      } else {
        showToast(err.message || 'Creation failed', 'error');
      }
      setIsSubmitting(false);
    }
  };

  // Update
  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingReservation) return;

    setIsSubmitting(true);
    setFormErrors({});

    try {
      await api.patch(`/reservations/${editingReservation.id}`, form);
      showToast('Reservation updated!');
      closeModals();
      refetch();
    } catch (err: any) {
      if (err.errors) {
        setFormErrors(err.errors);
      } else {
        showToast(err.message || 'Update failed', 'error');
      }
      setIsSubmitting(false);
    }
  };

  // Delete single
  const handleDelete = async (id: number) => {
    if (!confirm('Delete this reservation?')) return;
    try {
      await api.delete(`/reservations/${id}`);
      showToast('Reservation deleted.');
      refetch();
    } catch (err: any) {
      showToast(err.message || 'Delete failed', 'error');
    }
  };

  // Export placeholders
  const handleExportCSV = () => showToast('CSV export coming soon', 'info');
  const handleExportPDF = () => showToast('PDF export coming soon', 'info');

  // ---------- Render ----------
  return (
    <DashboardLayout>
      <div className="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
          <h3 className="fw-bold mb-1">Reservations</h3>
          <p className="text-muted-soft mb-0 small">Search, filter and manage every booking.</p>
        </div>
        <div className="d-flex flex-wrap gap-2">
          <button className="btn btn-dark-ghost btn-sm" onClick={handleExportCSV}>
            <i className="bi bi-filetype-csv me-1"></i>CSV
          </button>
          <button className="btn btn-dark-ghost btn-sm" onClick={handleExportPDF}>
            <i className="bi bi-file-earmark-pdf me-1"></i>PDF
          </button>
          <button
            className="btn btn-gold btn-sm"
            onClick={() => { setForm(defaultForm); setFormErrors({}); setIsCreateModalOpen(true); }}
          >
            <i className="bi bi-plus-lg me-1"></i>New reservation
          </button>
        </div>
      </div>

      {/* Filters */}
      <div className="card-elev p-3 mb-3">
        <div className="d-flex flex-wrap align-items-center gap-2 justify-content-between">
          <div className="d-flex flex-wrap gap-2" id="resTabs">
            {['all', 'upcoming', 'confirmed', 'completed', 'cancelled'].map((tab) => (
              <button
                key={tab}
                className={`btn btn-sm ${statusFilter === tab ? 'btn-gold' : 'btn-dark-ghost'} btn-sm-pill text-capitalize`}
                onClick={() => { setStatusFilter(tab); setPage(1); }}
              >
                {tab}
              </button>
            ))}
          </div>
          <div className="topbar-search" style={{ maxWidth: '280px' }}>
            <i className="bi bi-search"></i>
            <input
              type="text"
              placeholder="Search guest or table…"
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            />
          </div>
        </div>
      </div>

      {/* Bulk actions bar */}
      {selectedIds.length > 0 && (
        <div className="card-elev p-3 mb-3 d-flex align-items-center gap-3 flex-wrap">
          <span className="fw-semibold small">{selectedIds.length} selected</span>
          <div className="d-flex flex-wrap gap-2 ms-auto">
            <button className="btn btn-dark-ghost btn-sm" onClick={() => handleBulkAction('confirm')}>
              <i className="bi bi-check2 me-1"></i>Mark confirmed
            </button>
            <button className="btn btn-dark-ghost btn-sm" onClick={() => handleBulkAction('cancel')}>
              <i className="bi bi-x-circle me-1"></i>Cancel
            </button>
            <button className="btn btn-outline-danger btn-sm" onClick={() => handleBulkAction('delete')}>
              <i className="bi bi-trash me-1"></i>Delete
            </button>
            <button className="btn btn-outline-ghost btn-sm" onClick={() => setSelectedIds([])}>
              Clear
            </button>
          </div>
        </div>
      )}

      {/* Table */}
      <div className="card-elev">
        {loading ? (
          <div className="p-4 text-center">
            <div className="spinner-border" role="status"><span className="visually-hidden">Loading...</span></div>
          </div>
        ) : error ? (
          <div className="p-4 text-center text-rust">{error}</div>
        ) : reservations.length === 0 ? (
          <div className="p-4 text-center text-muted-soft">No reservations found.</div>
        ) : (
          <>
            <div className="table-responsive">
              <table className="table-app table-app-responsive">
                <thead>
                  <tr>
                    <th style={{ width: '36px' }}>
                      <input
                        type="checkbox"
                        className="form-check-input"
                        checked={selectedIds.length === reservations.length && reservations.length > 0}
                        onChange={(e) => toggleSelectAll(e.target.checked)}
                      />
                    </th>
                    <th>Guest</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Party</th>
                    <th>Table</th>
                    <th>Status</th>
                    <th>Source</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  {reservations.map((res) => (
                    <tr key={res.id} onClick={() => openEditModal(res)} style={{ cursor: 'pointer' }}>
                      <td data-label="" onClick={(e) => e.stopPropagation()}>
                        <input
                          type="checkbox"
                          className="form-check-input"
                          checked={selectedIds.includes(res.id)}
                          onChange={() => toggleSelect(res.id)}
                        />
                      </td>
                      <td data-label="Guest">
                        <div className="d-flex align-items-center gap-2">
                          <span className="avatar-sm">
                            {res.guest_name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()}
                          </span>
                          <div>
                            <div className="fw-semibold">{res.guest_name}</div>
                            <div className="text-faint" style={{ fontSize: '.72rem' }}>{res.public_ref}</div>
                          </div>
                        </div>
                      </td>
                      <td data-label="Date">{formatDate(res.date, timezone)}</td>
                      <td data-label="Time">{formatTime(res.time, timezone)}</td>
                      <td data-label="Party">{res.party_size}</td>
                      <td data-label="Table">{res.table?.code || '—'}</td>
                      <td data-label="Status"><StatusBadge status={res.status} /></td>
                      <td data-label="Source" className="text-muted-soft">{res.source}</td>
                      <td className="text-end" data-label="">
                        <div className="dropdown">
                          <button
                            className="icon-btn"
                            data-bs-toggle="dropdown"
                            onClick={(e) => e.stopPropagation()}
                          >
                            <i className="bi bi-three-dots-vertical"></i>
                          </button>
                          <ul className="dropdown-menu dropdown-menu-end shadow">
                            <li><a className="dropdown-item" href="#" onClick={(e) => { e.preventDefault(); openEditModal(res); }}>Edit</a></li>
                            <li><a className="dropdown-item" href="#" onClick={(e) => { e.preventDefault(); /* confirm */ }}>Mark confirmed</a></li>
                            <li><a className="dropdown-item" href="#" onClick={(e) => { e.preventDefault(); /* cancel */ }}>Cancel</a></li>
                            <li><hr className="dropdown-divider" /></li>
                            <li><a className="dropdown-item text-danger" href="#" onClick={(e) => { e.preventDefault(); handleDelete(res.id); }}>Delete</a></li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="d-flex align-items-center justify-content-between p-3 border-top" style={{ borderColor: 'var(--border) !important' }}>
              <span className="text-muted-soft small">
                Showing {reservations.length} of {meta.total} reservations
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

      {/* ======= CREATE MODAL ======= */}
      <div
        className={`modal fade ${isCreateModalOpen ? 'show d-block' : ''}`}
        id="newReservationModal"
        tabIndex={-1}
        style={{ display: isCreateModalOpen ? 'block' : 'none', background: isCreateModalOpen ? 'rgba(0,0,0,0.5)' : 'none' }}
        onClick={(e) => { if (e.target === e.currentTarget) closeModals(); }}
      >
        <div className="modal-dialog modal-dialog-centered modal-lg">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title fw-bold">New reservation</h5>
              <button type="button" className="btn-close" onClick={closeModals}></button>
            </div>
            <form onSubmit={handleCreate}>
              <div className="modal-body">
                <ReservationFormFields
                  form={form}
                  setForm={setForm}
                  errors={formErrors}
                  isSubmitting={isSubmitting}
                />
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-dark-ghost btn-sm" onClick={closeModals}>Cancel</button>
                <LoadingButton type="submit" isLoading={isSubmitting} className="btn btn-gold btn-sm">
                  Create reservation
                </LoadingButton>
              </div>
            </form>
          </div>
        </div>
      </div>

      {/* ======= EDIT MODAL ======= */}
      <div
        className={`modal fade ${isEditModalOpen ? 'show d-block' : ''}`}
        id="editReservationModal"
        tabIndex={-1}
        style={{ display: isEditModalOpen ? 'block' : 'none', background: isEditModalOpen ? 'rgba(0,0,0,0.5)' : 'none' }}
        onClick={(e) => { if (e.target === e.currentTarget) closeModals(); }}
      >
        <div className="modal-dialog modal-dialog-centered modal-lg">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title fw-bold">Edit reservation</h5>
              <button type="button" className="btn-close" onClick={closeModals}></button>
            </div>
            <form onSubmit={handleUpdate}>
              <div className="modal-body">
                <ReservationFormFields
                  form={form}
                  setForm={setForm}
                  errors={formErrors}
                  isSubmitting={isSubmitting}
                />
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-dark-ghost btn-sm" onClick={closeModals}>Cancel</button>
                <LoadingButton type="submit" isLoading={isSubmitting} className="btn btn-gold btn-sm">
                  Update reservation
                </LoadingButton>
              </div>
            </form>
          </div>
        </div>
      </div>

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}

// ---------- Reusable Form Fields ----------
interface FormFieldsProps {
  form: ReservationForm;
  setForm: React.Dispatch<React.SetStateAction<ReservationForm>>;
  errors: Record<string, string[]>;
  isSubmitting: boolean;
}

function ReservationFormFields({ form, setForm, errors, isSubmitting }: FormFieldsProps) {
  const handleChange = (field: keyof ReservationForm, value: any) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  return (
    <div className="row g-3">
      <div className="col-md-6">
        <label className="form-label" htmlFor="guest_name">Guest name *</label>
        <input
          id="guest_name"
          type="text"
          className={`form-control ${errors.guest_name ? 'is-invalid' : ''}`}
          value={form.guest_name}
          onChange={(e) => handleChange('guest_name', e.target.value)}
          disabled={isSubmitting}
          required
        />
        {errors.guest_name && <div className="invalid-feedback d-block">{errors.guest_name[0]}</div>}
      </div>

      <div className="col-md-6">
        <label className="form-label" htmlFor="guest_phone">Phone *</label>
        <input
          id="guest_phone"
          type="tel"
          className={`form-control ${errors.guest_phone ? 'is-invalid' : ''}`}
          value={form.guest_phone}
          onChange={(e) => handleChange('guest_phone', e.target.value)}
          disabled={isSubmitting}
          required
        />
        {errors.guest_phone && <div className="invalid-feedback d-block">{errors.guest_phone[0]}</div>}
      </div>

      <div className="col-md-6">
        <label className="form-label" htmlFor="guest_email">Email</label>
        <input
          id="guest_email"
          type="email"
          className={`form-control ${errors.guest_email ? 'is-invalid' : ''}`}
          value={form.guest_email}
          onChange={(e) => handleChange('guest_email', e.target.value)}
          disabled={isSubmitting}
        />
        {errors.guest_email && <div className="invalid-feedback d-block">{errors.guest_email[0]}</div>}
      </div>

      <div className="col-md-3">
        <label className="form-label" htmlFor="date">Date *</label>
        <input
          id="date"
          type="date"
          className={`form-control ${errors.date ? 'is-invalid' : ''}`}
          value={form.date}
          onChange={(e) => handleChange('date', e.target.value)}
          disabled={isSubmitting}
          required
        />
        {errors.date && <div className="invalid-feedback d-block">{errors.date[0]}</div>}
      </div>

      <div className="col-md-3">
        <label className="form-label" htmlFor="time">Time *</label>
        <input
          id="time"
          type="time"
          className={`form-control ${errors.time ? 'is-invalid' : ''}`}
          value={form.time}
          onChange={(e) => handleChange('time', e.target.value)}
          disabled={isSubmitting}
          required
        />
        {errors.time && <div className="invalid-feedback d-block">{errors.time[0]}</div>}
      </div>

      <div className="col-md-3">
        <label className="form-label" htmlFor="party_size">Party size *</label>
        <input
          id="party_size"
          type="number"
          min="1"
          className={`form-control ${errors.party_size ? 'is-invalid' : ''}`}
          value={form.party_size}
          onChange={(e) => handleChange('party_size', parseInt(e.target.value) || 1)}
          disabled={isSubmitting}
          required
        />
        {errors.party_size && <div className="invalid-feedback d-block">{errors.party_size[0]}</div>}
      </div>

      <div className="col-md-3">
        <label className="form-label" htmlFor="source">Source</label>
        <select
          id="source"
          className="form-select"
          value={form.source}
          onChange={(e) => handleChange('source', e.target.value)}
          disabled={isSubmitting}
        >
          <option value="Website">Website</option>
          <option value="Phone">Phone</option>
          <option value="App">App</option>
          <option value="Walk-in">Walk-in</option>
        </select>
      </div>

      <div className="col-12">
        <label className="form-label" htmlFor="notes">Notes</label>
        <textarea
          id="notes"
          className="form-control"
          rows={2}
          value={form.notes}
          onChange={(e) => handleChange('notes', e.target.value)}
          disabled={isSubmitting}
        />
      </div>

      <div className="col-12 text-muted-soft small">
        <i className="bi bi-info-circle me-1"></i>
        Table will be auto‑assigned if not specified.
      </div>
    </div>
  );
}