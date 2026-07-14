import React, { useState, useEffect } from 'react';
import LoadingButton from '@/Components/Common/LoadingButton';
import { api } from '@/lib/api';

interface Table {
  id: number;
  code: string;
  capacity: number;
  zone: string;
}

interface NewReservationModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  initialTableId?: number | null;
}

interface ReservationForm {
  guest_name: string;
  guest_phone: string;
  guest_email: string;
  date: string;
  time: string;
  party_size: number;
  table_id: number | null;
  notes: string;
}

export default function NewReservationModal({ isOpen, onClose, onSuccess, initialTableId }: NewReservationModalProps) {
  const [form, setForm] = useState<ReservationForm>({
    guest_name: '',
    guest_phone: '',
    guest_email: '',
    date: new Date().toISOString().split('T')[0],
    time: '19:00',
    party_size: 2,
    table_id: null,
    notes: '',
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [tables, setTables] = useState<Table[]>([]);

  useEffect(() => {
    if (isOpen) {
      api.get('/tables').then((res: any) => {
        // ✅ FIXED: res.data is the array of tables
        setTables(res.data || []);
      });
      if (initialTableId) {
        setForm(prev => ({ ...prev, table_id: initialTableId }));
      }
      setErrors({});
    }
  }, [isOpen, initialTableId]);

  const handleChange = (field: keyof ReservationForm, value: any) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});

    try {
      // Build payload without table_id if null
      const payload: any = { ...form };
      if (payload.table_id === null) {
        delete payload.table_id;
      }
      await api.post('/reservations', payload);
      onSuccess();
      onClose();
    } catch (err: any) {
      if (err.errors) {
        setErrors(err.errors);
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div
      className="modal fade show d-block"
      tabIndex={-1}
      style={{ display: 'block', background: 'rgba(0,0,0,0.5)' }}
      onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
    >
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title fw-bold">New Reservation</h5>
            <button type="button" className="btn-close" onClick={onClose}></button>
          </div>
          <form onSubmit={handleSubmit}>
            <div className="modal-body">
              <div className="row g-3">
                <div className="col-12">
                  <label className="form-label">Guest Name *</label>
                  <input
                    type="text"
                    className={`form-control ${errors.guest_name ? 'is-invalid' : ''}`}
                    value={form.guest_name}
                    onChange={(e) => handleChange('guest_name', e.target.value)}
                    required
                  />
                  {errors.guest_name && <div className="invalid-feedback d-block">{errors.guest_name[0]}</div>}
                </div>
                <div className="col-12">
                  <label className="form-label">Phone *</label>
                  <input
                    type="tel"
                    className={`form-control ${errors.guest_phone ? 'is-invalid' : ''}`}
                    value={form.guest_phone}
                    onChange={(e) => handleChange('guest_phone', e.target.value)}
                    required
                  />
                  {errors.guest_phone && <div className="invalid-feedback d-block">{errors.guest_phone[0]}</div>}
                </div>
                <div className="col-12">
                  <label className="form-label">Email</label>
                  <input
                    type="email"
                    className={`form-control ${errors.guest_email ? 'is-invalid' : ''}`}
                    value={form.guest_email}
                    onChange={(e) => handleChange('guest_email', e.target.value)}
                  />
                  {errors.guest_email && <div className="invalid-feedback d-block">{errors.guest_email[0]}</div>}
                </div>
                <div className="col-6">
                  <label className="form-label">Date *</label>
                  <input
                    type="date"
                    className={`form-control ${errors.date ? 'is-invalid' : ''}`}
                    value={form.date}
                    onChange={(e) => handleChange('date', e.target.value)}
                    required
                  />
                  {errors.date && <div className="invalid-feedback d-block">{errors.date[0]}</div>}
                </div>
                <div className="col-6">
                  <label className="form-label">Time *</label>
                  <input
                    type="time"
                    className={`form-control ${errors.time ? 'is-invalid' : ''}`}
                    value={form.time}
                    onChange={(e) => handleChange('time', e.target.value)}
                    required
                  />
                  {errors.time && <div className="invalid-feedback d-block">{errors.time[0]}</div>}
                </div>
                <div className="col-6">
                  <label className="form-label">Party Size *</label>
                  <input
                    type="number"
                    min="1"
                    className={`form-control ${errors.party_size ? 'is-invalid' : ''}`}
                    value={form.party_size}
                    onChange={(e) => handleChange('party_size', parseInt(e.target.value) || 1)}
                    required
                  />
                  {errors.party_size && <div className="invalid-feedback d-block">{errors.party_size[0]}</div>}
                </div>
                <div className="col-6">
                  <label className="form-label">Table</label>
                  <select
                    className="form-select"
                    value={form.table_id || ''}
                    onChange={(e) => handleChange('table_id', e.target.value ? parseInt(e.target.value) : null)}
                  >
                    <option value="">Auto-assign</option>
                    {tables.map((table) => (
                      <option key={table.id} value={table.id}>
                        {table.code} ({table.capacity} seats)
                      </option>
                    ))}
                  </select>
                </div>
                <div className="col-12">
                  <label className="form-label">Notes</label>
                  <textarea
                    className="form-control"
                    rows={2}
                    value={form.notes}
                    onChange={(e) => handleChange('notes', e.target.value)}
                  />
                </div>
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-dark-ghost btn-sm" onClick={onClose}>Cancel</button>
              <LoadingButton type="submit" isLoading={isSubmitting} className="btn btn-gold btn-sm">
                Create Reservation
              </LoadingButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}