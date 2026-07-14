import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import { api } from '@/lib/api';
import Toast from '@/Components/Common/Toast';

export default function Booking() {
  const [form, setForm] = useState({
    guest_name: '',
    guest_phone: '',
    guest_email: '',
    date: new Date().toISOString().split('T')[0],
    time: '19:00',
    party_size: 2,
    notes: '',
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState<{ ref: string; message: string } | null>(null);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  const handleChange = (field: string, value: any) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    setSuccess(null);

    try {
      const response = await api.post('/api/v1/public/reservations', form);
      setSuccess({
        ref: response.data.data.public_ref,
        message: response.data.message,
      });
      setToast({ message: 'Booking confirmed!', type: 'success' });
    } catch (err: any) {
      if (err.errors) {
        setErrors(err.errors);
      } else {
        setToast({ message: err.message || 'Something went wrong', type: 'error' });
      }
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <>
        <Head title="Booking Confirmed" />
        <div className="login-shell">
          <div className="d-flex flex-column justify-content-center align-items-center p-4" style={{ width: '100%', maxWidth: '480px', margin: '0 auto' }}>
            <div className="text-center">
              <i className="bi bi-check-circle-fill text-emerald" style={{ fontSize: '3rem' }}></i>
              <h3 className="fw-bold mt-3">Booking confirmed!</h3>
              <p className="text-muted-soft">Your reference: <strong className="text-gold">{success.ref}</strong></p>
              <p className="small text-muted-soft">We've sent a confirmation email to you.</p>
              <a href="/" className="btn btn-gold mt-3">Return home</a>
            </div>
          </div>
        </div>
      </>
    );
  }

  return (
    <>
      <Head title="Book a table" />
      <div className="login-shell">
        <div className="d-flex flex-column justify-content-center align-items-center p-4" style={{ width: '100%', maxWidth: '480px', margin: '0 auto' }}>
          <div className="mb-4 text-center">
            <span className="brand-mark"><i className="bi bi-egg-fried"></i></span>
            <h1 className="fw-bold mt-2">Book a table</h1>
            <p className="text-muted-soft small">Fill in your details and we'll confirm your booking.</p>
          </div>

          <form onSubmit={handleSubmit} className="w-100">
            <div className="mb-3">
              <label className="form-label">Full name *</label>
              <input
                type="text"
                className={`form-control ${errors.guest_name ? 'is-invalid' : ''}`}
                value={form.guest_name}
                onChange={(e) => handleChange('guest_name', e.target.value)}
                required
              />
              {errors.guest_name && <div className="invalid-feedback d-block">{errors.guest_name[0]}</div>}
            </div>

            <div className="mb-3">
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

            <div className="mb-3">
              <label className="form-label">Email *</label>
              <input
                type="email"
                className={`form-control ${errors.guest_email ? 'is-invalid' : ''}`}
                value={form.guest_email}
                onChange={(e) => handleChange('guest_email', e.target.value)}
                required
              />
              {errors.guest_email && <div className="invalid-feedback d-block">{errors.guest_email[0]}</div>}
            </div>

            <div className="row g-3 mb-3">
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
            </div>

            <div className="mb-3">
              <label className="form-label">Party size *</label>
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

            <div className="mb-3">
              <label className="form-label">Special requests (optional)</label>
              <textarea
                className="form-control"
                rows={2}
                value={form.notes}
                onChange={(e) => handleChange('notes', e.target.value)}
              />
            </div>

            <button type="submit" className="btn btn-gold w-100" disabled={loading}>
              {loading ? 'Submitting...' : 'Book now'}
            </button>
          </form>

          {toast && <Toast message={toast.message} type={toast.type} />}
        </div>
      </div>
    </>
  );
}