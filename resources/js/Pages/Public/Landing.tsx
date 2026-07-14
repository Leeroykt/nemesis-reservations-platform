import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Landing() {
  return (
    <>
      <Head title="Welcome to Savora" />
      <div className="login-shell">
        {/* Left side - Content */}
        <div className="col-lg-6 d-flex flex-column justify-content-center align-items-center p-4 p-md-5 position-relative">
          <div className="login-form-wrap fade-up" style={{ maxWidth: '480px', width: '100%' }}>
            {/* Centered Logo */}
            <div className="text-center mb-4">
              <div className="d-inline-flex align-items-center gap-3 mb-3">
                <span className="brand-mark" style={{ 
                  width: '56px', 
                  height: '56px', 
                  fontSize: '1.4rem',
                  borderRadius: '14px',
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
                <span className="brand-word fs-1" style={{ 
                  fontFamily: 'var(--font-display)', 
                  fontStyle: 'italic',
                  fontWeight: 600,
                  color: 'var(--text)',
                }}>
                  Savora
                </span>
              </div>
              <p className="text-muted-soft mt-2" style={{ fontSize: '1.1rem' }}>
                Book a table at the finest restaurants in town.
              </p>
            </div>

            <div className="credentials-card mb-4">
              <div className="d-flex align-items-center justify-content-center gap-2 mb-3">
                <i className="bi bi-shield-check text-emerald"></i>
                <span className="small fw-semibold">
                  <span className="status-dot confirmed" style={{ marginRight: '8px' }}></span>
                  Secure Booking
                </span>
              </div>
              <div className="row g-2">
                {/* Instant Confirmation */}
                <div className="col-6">
                  <div className="text-center p-3" style={{ background: 'var(--surface-2)', borderRadius: '12px' }}>
                    <div style={{ fontSize: '1.8rem', color: 'var(--gold)' }}>
                      <i className="bi bi-clock-history"></i>
                    </div>
                    <div className="small text-muted-soft mt-1">Instant Confirmation</div>
                  </div>
                </div>
                {/* Email Reminders */}
                <div className="col-6">
                  <div className="text-center p-3" style={{ background: 'var(--surface-2)', borderRadius: '12px' }}>
                    <div style={{ fontSize: '1.8rem', color: 'var(--emerald)' }}>
                      <i className="bi bi-envelope-paper"></i>
                    </div>
                    <div className="small text-muted-soft mt-1">Email Reminders</div>
                  </div>
                </div>
                {/* Easy Cancellation */}
                <div className="col-6">
                  <div className="text-center p-3" style={{ background: 'var(--surface-2)', borderRadius: '12px' }}>
                    <div style={{ fontSize: '1.8rem', color: 'var(--slate)' }}>
                      <i className="bi bi-arrow-repeat"></i>
                    </div>
                    <div className="small text-muted-soft mt-1">Easy Cancellation</div>
                  </div>
                </div>
                {/* VIP Perks */}
                <div className="col-6">
                  <div className="text-center p-3" style={{ background: 'var(--surface-2)', borderRadius: '12px' }}>
                    <div style={{ fontSize: '1.8rem', color: 'var(--gold)' }}>
                      <i className="bi bi-star-fill"></i>
                    </div>
                    <div className="small text-muted-soft mt-1">VIP Perks</div>
                  </div>
                </div>
              </div>
            </div>

            <Link href="/book" className="btn btn-gold btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
              <i className="bi bi-calendar-plus"></i>
              Book a table
            </Link>

            <div className="mt-4 text-center">
              <span className="text-faint small">© 2026 Savora — a NEMESIS product.</span>
            </div>
          </div>
        </div>

        {/* Right side - Hero section */}
        <div className="col-lg-6 login-aside d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative">
          <div></div>
          <div className="position-relative fade-up" style={{ zIndex: 2 }}>
            <div className="eyebrow mb-3">Fine Dining Experience</div>
            <h2 className="fw-bold mb-4" style={{ fontSize: '2.2rem', maxWidth: '480px' }}>
              Reserve your table in seconds.
              <br />
              <span className="text-gold">No hassle, no waiting.</span>
            </h2>
            <div className="glass rounded-4 p-4" style={{ maxWidth: '420px' }}>
              <div className="d-flex align-items-center gap-3 mb-3">
                <div style={{ fontSize: '1.6rem', color: 'var(--gold)' }}>
                  <i className="bi bi-lightning-fill"></i>
                </div>
                <div>
                  <div className="fw-semibold">Simple & Fast</div>
                  <div className="text-faint small">Book in under 60 seconds</div>
                </div>
              </div>
              <div className="d-flex align-items-center gap-3 mb-3">
                <div style={{ fontSize: '1.6rem', color: 'var(--emerald)' }}>
                  <i className="bi bi-phone"></i>
                </div>
                <div>
                  <div className="fw-semibold">Mobile Friendly</div>
                  <div className="text-faint small">Book from any device</div>
                </div>
              </div>
              <div className="d-flex align-items-center gap-3">
                <div style={{ fontSize: '1.6rem', color: 'var(--gold)' }}>
                  <i className="bi bi-bell-fill"></i>
                </div>
                <div>
                  <div className="fw-semibold">Real-time Updates</div>
                  <div className="text-faint small">Instant confirmation & reminders</div>
                </div>
              </div>
            </div>
          </div>
          <div className="small text-faint position-relative" style={{ zIndex: 2 }}>
            © 2026 Savora — a NEMESIS product.
          </div>
        </div>
      </div>
    </>
  );
}