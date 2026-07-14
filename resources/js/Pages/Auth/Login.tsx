import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { api } from '@/lib/api';

export default function Login() {
  const [email, setEmail] = useState('owner@signetandvine.co.zw');
  const [password, setPassword] = useState('Demo123!');
  const [remember, setRemember] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [generalError, setGeneralError] = useState<string | null>(null);
  const [errors, setErrors] = useState<{ email?: string; password?: string }>({});
  const [showPassword, setShowPassword] = useState(false);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);
    setGeneralError(null);
    setErrors({});
    try {
      await api.login(email, password);
      router.visit('/dashboard/overview');
    } catch (err: any) {
      if (err.errors) {
        setErrors(err.errors);
      } else {
        setGeneralError(err.message || 'Invalid credentials. Please try again.');
      }
    } finally {
      setProcessing(false);
    }
  };

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  return (
    <>
      <Head title="Sign in" />
      <div className="login-shell">
        <div className="col-lg-6 d-flex flex-column justify-content-center align-items-center p-4 p-md-5 position-relative">
          <Link href="/" className="position-absolute top-0 start-0 m-4 d-flex align-items-center gap-2 text-decoration-none">
            <span className="brand-mark"><i className="bi bi-egg-fried"></i></span>
            <span className="brand-word fs-5">Savora</span>
          </Link>

          <button className="btn icon-btn position-absolute top-0 end-0 m-4" data-theme-toggle title="Toggle theme">
            <span data-theme-thumb><i className="bi bi-moon-stars-fill"></i></span>
          </button>

          <div className="login-form-wrap fade-up">
            <div className="mb-4">
              <h1 className="fw-bold mb-2" style={{ fontSize: '1.9rem' }}>Welcome back</h1>
              <p className="text-muted-soft mb-0">Sign in to manage your restaurant's floor.</p>
            </div>

            <div className="credentials-card mb-4 py-3 px-3">
              <div className="d-flex align-items-center gap-2 mb-2">
                <i className="bi bi-shield-check text-emerald"></i>
                <span className="small fw-semibold">
                  <span className="status-dot confirmed" style={{ marginRight: '8px' }}></span>
                  Enterprise Edition
                </span>
              </div>
              <div className="small text-muted-soft">
                Secure, role‑based access for owners, managers, and hosts.
              </div>
            </div>

            <form onSubmit={submit}>
              <div className="mb-3">
                <label className="form-label" htmlFor="email">Email</label>
                <div className="input-group-flat">
                  <i className="bi bi-envelope"></i>
                  <input
                    type="email"
                    className="form-control"
                    id="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                  />
                </div>
                {errors.email && <div className="text-danger small mt-1">{errors.email}</div>}
              </div>

              <div className="mb-3">
                <label className="form-label" htmlFor="password">Password</label>
                <div className="input-group-flat">
                  <i className="bi bi-lock"></i>
                  <input
                    type={showPassword ? 'text' : 'password'}
                    className="form-control"
                    id="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                  />
                  <i 
                    className={`bi ${showPassword ? 'bi-eye-slash' : 'bi-eye'} toggle-eye`} 
                    id="toggleEye"
                    onClick={togglePasswordVisibility}
                    style={{ cursor: 'pointer' }}
                  ></i>
                </div>
                {errors.password && <div className="text-danger small mt-1">{errors.password}</div>}
              </div>

              <div className="d-flex align-items-center justify-content-between mb-4">
                <div className="form-check">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    id="remember"
                    checked={remember}
                    onChange={(e) => setRemember(e.target.checked)}
                  />
                  <label className="form-check-label small text-muted-soft" htmlFor="remember">Keep me signed in</label>
                </div>
                <Link href="/forgot-password" className="small text-faint text-decoration-none">
                  Forgot password?
                </Link>
              </div>

              <button type="submit" className="btn btn-gold w-100 btn-lg d-flex align-items-center justify-content-center gap-2" disabled={processing}>
                <span>{processing ? 'Signing in...' : 'Sign in'}</span>
                {processing && <span className="spinner-border spinner-border-sm"></span>}
              </button>

              {generalError && (
                <div className="text-danger small mt-3">
                  <i className="bi bi-exclamation-triangle me-1"></i>
                  {generalError}
                </div>
              )}
            </form>
          </div>
        </div>

        <div className="col-lg-6 login-aside d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative">
          <div></div>
          <div className="position-relative fade-up" style={{ zIndex: 2 }}>
            <div className="eyebrow mb-3">Tonight at Signet &amp; Vine</div>
            <h2 className="fw-bold mb-4" style={{ fontSize: '2rem', maxWidth: '480px' }}>Every table accounted for, before the first guest walks in.</h2>
            <div className="glass rounded-4 p-4" style={{ maxWidth: '420px' }}>
              <div className="ticker-row"><span className="status-dot confirmed"></span><span className="flex-grow-1">Farai Chikono · Table T-12</span><span className="text-faint">7:30pm</span></div>
              <div className="ticker-row"><span className="status-dot upcoming"></span><span className="flex-grow-1">Rutendo Chirwa · Table T-05</span><span className="text-faint">6:00pm</span></div>
              <div className="ticker-row"><span className="status-dot confirmed"></span><span className="flex-grow-1">Tanaka Moyo · Table T-18</span><span className="text-faint">8:00pm</span></div>
              <div className="ticker-row"><span className="status-dot cancelled"></span><span className="flex-grow-1">Chiedza Mutasa · Table T-13</span><span className="text-faint">8:00pm</span></div>
            </div>
          </div>
          <div className="small text-faint position-relative" style={{ zIndex: 2 }}>© 2026 Savora — a NEMESIS product.</div>
        </div>
      </div>
    </>
  );
}