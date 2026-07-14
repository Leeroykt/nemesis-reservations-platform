import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { api } from '@/lib/api';
import Toast from '@/Components/Common/Toast';

interface BookingResponse {
  public_ref: string;
  guest_name: string;
  date: string;
  time: string;
  party_size: number;
}

interface ApiResponse<T> {
  data: T;
  message?: string;
}

export default function BookingForm() {
  // Get tomorrow's date for min date
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const minDate = tomorrow.toISOString().split('T')[0];

  const [form, setForm] = useState({
    guest_name: '',
    guest_phone: '',
    guest_email: '',
    date: minDate,
    time: '19:00',
    party_size: 2,
    notes: '',
  });
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState<{
    ref: string;
    guest_name: string;
    date: string;
    time: string;
    party_size: number;
    message: string;
  } | null>(null);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  // Frontend validation before submission
  const validateForm = (): boolean => {
    const newErrors: Record<string, string[]> = {};

    if (!form.guest_name.trim()) {
      newErrors.guest_name = ['Full name is required.'];
    } else if (form.guest_name.length > 120) {
      newErrors.guest_name = ['Full name cannot exceed 120 characters.'];
    }

    if (!form.guest_phone.trim()) {
      newErrors.guest_phone = ['Phone number is required.'];
    } else if (form.guest_phone.length > 40) {
      newErrors.guest_phone = ['Phone number cannot exceed 40 characters.'];
    }

    if (!form.guest_email.trim()) {
      newErrors.guest_email = ['Email address is required.'];
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.guest_email)) {
      newErrors.guest_email = ['Please enter a valid email address.'];
    } else if (form.guest_email.length > 160) {
      newErrors.guest_email = ['Email cannot exceed 160 characters.'];
    }

    if (!form.date) {
      newErrors.date = ['Please select a date.'];
    } else {
      const selectedDate = new Date(form.date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      if (selectedDate < today) {
        newErrors.date = ['Date cannot be in the past.'];
      }
    }

    if (!form.time) {
      newErrors.time = ['Please select a time.'];
    } else if (!/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/.test(form.time)) {
      newErrors.time = ['Please enter a valid time.'];
    }

    if (!form.party_size || form.party_size < 1) {
      newErrors.party_size = ['Party size must be at least 1.'];
    } else if (form.party_size > 14) {
      newErrors.party_size = ['Party size cannot exceed 14 guests.'];
    }

    if (form.notes && form.notes.length > 500) {
      newErrors.notes = ['Notes cannot exceed 500 characters.'];
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleChange = (field: string, value: any) => {
    if (errors[field]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      setToast({ 
        message: 'Please fix the errors before submitting.', 
        type: 'error' 
      });
      const firstError = document.querySelector('.is-invalid');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }

    setLoading(true);
    setSuccess(null);

    try {
      const response = await api.post<BookingResponse>('/public/reservations', form);
      
      // ✅ CORRECT: response.data is BookingResponse, response.message is the message
      setSuccess({
        ref: response.data.public_ref,
        guest_name: response.data.guest_name,
        date: response.data.date,
        time: response.data.time,
        party_size: response.data.party_size,
        message: response.message || 'Booking confirmed!',
      });
      setToast({ message: 'Booking confirmed!', type: 'success' });
    } catch (err: any) {
      if (err.errors) {
        setErrors(err.errors);
        setToast({ 
          message: 'Please fix the errors below.', 
          type: 'error' 
        });
        const firstError = document.querySelector('.is-invalid');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      } else {
        setToast({ 
          message: err.message || 'Something went wrong. Please try again.', 
          type: 'error' 
        });
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
          <div className="d-flex flex-column justify-content-center align-items-center p-4 p-md-5" style={{ width: '100%', maxWidth: '560px', margin: '0 auto' }}>
            <div className="text-center w-100">
              <div 
                className="d-flex align-items-center justify-content-center mx-auto mb-4"
                style={{ 
                  width: '80px', 
                  height: '80px', 
                  borderRadius: '50%', 
                  background: 'rgba(63, 166, 114, 0.15)',
                }}
              >
                <i className="bi bi-check-circle-fill text-emerald" style={{ fontSize: '3rem' }}></i>
              </div>

              <h2 className="fw-bold mb-2">Booking Confirmed! 🎉</h2>
              <p className="text-muted-soft mb-4">
                Thank you, <strong>{success.guest_name}</strong>! Your table has been reserved.
              </p>

              <div className="credentials-card mb-4 text-start">
                <div className="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3" style={{ borderColor: 'var(--border) !important' }}>
                  <span className="text-muted-soft small">Booking Reference</span>
                  <span className="fw-bold text-gold" style={{ fontSize: '1.1rem' }}>{success.ref}</span>
                </div>
                <div className="row g-3">
                  <div className="col-6">
                    <div className="text-muted-soft small">Date</div>
                    <div className="fw-semibold">{new Date(success.date).toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</div>
                  </div>
                  <div className="col-6">
                    <div className="text-muted-soft small">Time</div>
                    <div className="fw-semibold">{success.time}</div>
                  </div>
                  <div className="col-6">
                    <div className="text-muted-soft small">Party Size</div>
                    <div className="fw-semibold">{success.party_size} {success.party_size === 1 ? 'guest' : 'guests'}</div>
                  </div>
                  <div className="col-6">
                    <div className="text-muted-soft small">Status</div>
                    <div>
                      <span className="badge-status confirmed">
                        <span className="status-dot confirmed"></span>
                        Confirmed
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div className="mb-4 p-3 rounded-3" style={{ background: 'var(--surface-2)', border: '1px solid var(--border)' }}>
                <div className="d-flex align-items-center gap-2 justify-content-center">
                  <i className="bi bi-envelope-check text-emerald"></i>
                  <span className="small text-muted-soft">
                    A confirmation email has been sent to your inbox.
                  </span>
                </div>
              </div>

              <div className="d-flex flex-column flex-sm-row gap-3">
                <Link href="/" className="btn btn-gold flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                  <i className="bi bi-house"></i>
                  Return Home
                </Link>
                <Link href="/book" className="btn btn-outline-ghost flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                  <i className="bi bi-calendar-plus"></i>
                  Book Another Table
                </Link>
              </div>

              <div className="mt-4 text-center">
                <span className="text-faint small">© 2026 Savora — a NEMESIS product.</span>
              </div>
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
        <div className="d-flex flex-column justify-content-center align-items-center p-4" style={{ width: '100%', maxWidth: '480px', margin: '0 auto', position: 'relative' }}>
          {/* Loading Overlay - Enterprise Grade */}
          {loading && (
            <div 
              style={{
                position: 'absolute',
                inset: 0,
                background: 'rgba(24, 38, 32, 0.85)',
                backdropFilter: 'blur(4px)',
                zIndex: 100,
                borderRadius: '16px',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
              }}
            >
              <div 
                className="spinner-border text-gold" 
                role="status"
                style={{ width: '3rem', height: '3rem' }}
              >
                <span className="visually-hidden">Submitting your booking...</span>
              </div>
              <p className="text-gold mt-3 fw-semibold" style={{ fontSize: '1.1rem' }}>
                Submitting your booking...
              </p>
              <p className="text-muted-soft small mt-1">
                Please wait, this will only take a moment.
              </p>
            </div>
          )}

          {/* Centered Logo */}
          <div className="text-center mb-4">
            <div className="d-inline-flex align-items-center gap-3 mb-2">
              <span className="brand-mark" style={{ 
                width: '48px', 
                height: '48px', 
                fontSize: '1.2rem',
                borderRadius: '12px',
                background: 'linear-gradient(160deg, var(--gold-soft), var(--gold))',
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                color: '#1B1204',
                fontWeight: 800,
                flexShrink: 0,
              }}>
                <i className="bi bi-egg-fried"></i>
              </span>
              <span className="brand-word fs-2" style={{ 
                fontFamily: 'var(--font-display)', 
                fontStyle: 'italic',
                fontWeight: 600,
                color: 'var(--text)',
              }}>
                Savora
              </span>
            </div>
            <p className="text-muted-soft small">Fill in your details and we'll confirm your booking.</p>
          </div>

          <form onSubmit={handleSubmit} className="w-100" noValidate>
            {/* Name Field */}
            <div className="mb-3">
              <label className="form-label">Full name *</label>
              <input
                type="text"
                className={`form-control ${errors.guest_name ? 'is-invalid' : ''}`}
                value={form.guest_name}
                onChange={(e) => handleChange('guest_name', e.target.value)}
                placeholder="Enter your full name"
                disabled={loading}
                required
              />
              {errors.guest_name && <div className="invalid-feedback d-block">{errors.guest_name[0]}</div>}
            </div>

            {/* Phone Field */}
            <div className="mb-3">
              <label className="form-label">Phone *</label>
              <input
                type="tel"
                className={`form-control ${errors.guest_phone ? 'is-invalid' : ''}`}
                value={form.guest_phone}
                onChange={(e) => handleChange('guest_phone', e.target.value)}
                placeholder="+263 77 123 4567"
                disabled={loading}
                required
              />
              {errors.guest_phone && <div className="invalid-feedback d-block">{errors.guest_phone[0]}</div>}
            </div>

            {/* Email Field */}
            <div className="mb-3">
              <label className="form-label">Email *</label>
              <input
                type="email"
                className={`form-control ${errors.guest_email ? 'is-invalid' : ''}`}
                value={form.guest_email}
                onChange={(e) => handleChange('guest_email', e.target.value)}
                placeholder="you@example.com"
                disabled={loading}
                required
              />
              {errors.guest_email && <div className="invalid-feedback d-block">{errors.guest_email[0]}</div>}
            </div>

            {/* Date & Time */}
            <div className="row g-3 mb-3">
              <div className="col-6">
                <label className="form-label">Date *</label>
                <input
                  type="date"
                  className={`form-control ${errors.date ? 'is-invalid' : ''}`}
                  value={form.date}
                  onChange={(e) => handleChange('date', e.target.value)}
                  min={minDate}
                  disabled={loading}
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
                  disabled={loading}
                  required
                />
                {errors.time && <div className="invalid-feedback d-block">{errors.time[0]}</div>}
              </div>
            </div>

            {/* Party Size */}
            <div className="mb-3">
              <label className="form-label">Party size *</label>
              <input
                type="number"
                min="1"
                max="14"
                className={`form-control ${errors.party_size ? 'is-invalid' : ''}`}
                value={form.party_size}
                onChange={(e) => handleChange('party_size', parseInt(e.target.value) || 1)}
                disabled={loading}
                required
              />
              {errors.party_size && <div className="invalid-feedback d-block">{errors.party_size[0]}</div>}
              <div className="text-faint small mt-1">Maximum 14 guests per booking.</div>
            </div>

            {/* Notes */}
            <div className="mb-3">
              <label className="form-label">Special requests (optional)</label>
              <textarea
                className={`form-control ${errors.notes ? 'is-invalid' : ''}`}
                rows={2}
                value={form.notes}
                onChange={(e) => handleChange('notes', e.target.value)}
                placeholder="Any special requests? (Dietary, seating preferences, etc.)"
                disabled={loading}
                maxLength={500}
              />
              {errors.notes && <div className="invalid-feedback d-block">{errors.notes[0]}</div>}
              <div className="text-faint small text-end mt-1">{form.notes.length}/500</div>
            </div>

            {/* Submit Button - Gold with loading state */}
            <button 
              type="submit" 
              className="btn btn-gold w-100" 
              disabled={loading}
              style={{
                opacity: loading ? 0.7 : 1,
                transition: 'opacity 0.2s ease',
              }}
            >
              {loading ? (
                <>
                  <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  Submitting...
                </>
              ) : (
                'Book now'
              )}
            </button>

            {/* Error Summary - Enterprise Grade */}
            {Object.keys(errors).length > 0 && (
              <div 
                className="mt-3 p-3 rounded-3"
                style={{ 
                  background: 'rgba(193, 80, 61, 0.1)', 
                  border: '1px solid var(--rust)',
                  borderRadius: '12px',
                }}
              >
                <div className="d-flex align-items-start gap-2">
                  <i className="bi bi-exclamation-triangle text-rust mt-1"></i>
                  <div>
                    <div className="small fw-semibold text-rust">Please fix the following errors:</div>
                    <ul className="small text-rust mb-0 ps-3" style={{ listStyleType: 'disc' }}>
                      {Object.values(errors).flat().map((error, index) => (
                        <li key={index}>{error}</li>
                      ))}
                    </ul>
                  </div>
                </div>
              </div>
            )}
          </form>

          {toast && <Toast message={toast.message} type={toast.type} />}
        </div>
      </div>
    </>
  );
}