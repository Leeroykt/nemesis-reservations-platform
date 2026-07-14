import React, { useState, useEffect } from 'react';
import { usePage, Link } from '@inertiajs/react';

interface TopbarProps {
  user: any;
}

export default function Topbar({ user }: TopbarProps) {
  const [theme, setTheme] = useState<'dark' | 'light'>(() => {
    return (localStorage.getItem('savora-theme') as 'dark' | 'light') || 'dark';
  });

  useEffect(() => {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('savora-theme', theme);
  }, [theme]);

  const toggleTheme = () => setTheme(theme === 'dark' ? 'light' : 'dark');

  const userInitials = user?.name?.split(' ').map((w: string) => w[0]).slice(0, 2).join('').toUpperCase() || '??';

  return (
    <header className="topbar">
      <i className="bi bi-list fs-4 d-lg-none" style={{ cursor: 'pointer' }} id="mobileMenuBtn" />
      <div className="topbar-search d-none d-sm-block">
        <i className="bi bi-search"></i>
        <input type="text" placeholder="Search reservations, guests, tables…" id="globalSearch" />
      </div>

      <div className="ms-auto d-flex align-items-center gap-2">
        <button className="icon-btn" onClick={toggleTheme} title="Toggle theme">
          <span data-theme-thumb>
            {theme === 'dark' ? <i className="bi bi-moon-stars-fill"></i> : <i className="bi bi-sun-fill"></i>}
          </span>
        </button>

        <div className="dropdown">
          <button className="icon-btn" data-bs-toggle="dropdown">
            <i className="bi bi-bell"></i>
            <span className="dot-badge" id="notifDot"></span>
          </button>
          <div className="dropdown-menu dropdown-menu-end shadow p-0" style={{ width: '340px' }}>
            <div className="d-flex align-items-center justify-content-between p-3 border-bottom">
              <span className="fw-semibold">Notifications</span>
              <span className="small text-gold" style={{ cursor: 'pointer' }}>Mark all read</span>
            </div>
            <div id="notifList" style={{ maxHeight: '360px', overflowY: 'auto' }}>
              {/* Notifications will be fetched and rendered here */}
            </div>
          </div>
        </div>

        <div className="dropdown d-none d-sm-block">
          <div className="avatar-circle" style={{ cursor: 'pointer' }} data-bs-toggle="dropdown">
            {userInitials}
          </div>
          <ul className="dropdown-menu dropdown-menu-end shadow">
            <li><Link className="dropdown-item" href="/dashboard/settings"><i className="bi bi-person me-2"></i>Profile</Link></li>
            <li><Link className="dropdown-item" href="/dashboard/settings"><i className="bi bi-gear me-2"></i>Settings</Link></li>
            <li><hr className="dropdown-divider" /></li>
            <li><Link className="dropdown-item text-danger" href="/logout"><i className="bi bi-box-arrow-right me-2"></i>Sign out</Link></li>
          </ul>
        </div>
      </div>
    </header>
  );
}