import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

const navItems = [
  { route: 'overview', label: 'Overview', icon: 'bi-grid-1x2', role: 'host' },
  { route: 'reservations', label: 'Reservations', icon: 'bi-calendar-check', role: 'host' },
  { route: 'calendar', label: 'Calendar', icon: 'bi-calendar3', role: 'host' },
  { route: 'tables', label: 'Tables', icon: 'bi-diagram-3', role: 'host' },
  { route: 'customers', label: 'Customers', icon: 'bi-people', role: 'manager' },
  { route: 'analytics', label: 'Analytics', icon: 'bi-graph-up', role: 'manager' },
  { route: 'settings', label: 'Settings', icon: 'bi-gear', role: 'owner' },
  { route: 'audit', label: 'Audit log', icon: 'bi-journal-text', role: 'owner' },
];

const roleLevels: Record<string, number> = {
  host: 1,
  manager: 2,
  owner: 3,
};

interface SidebarProps {
  user: any;
}

export default function Sidebar({ user }: SidebarProps) {
  const { url } = usePage();
  const [collapsed, setCollapsed] = useState(false);

  const userRole = (user?.role as keyof typeof roleLevels) || 'host';
  const userInitials = user?.name?.split(' ').map((w: string) => w[0]).slice(0, 2).join('').toUpperCase() || '??';

  const hasAccess = (minRole: string) => {
    const userLevel = roleLevels[userRole] || 0;
    const minLevel = roleLevels[minRole as keyof typeof roleLevels] || 0;
    return userLevel >= minLevel;
  };

  return (
    <aside className={`sidebar ${collapsed ? 'collapsed' : ''}`}>
      <div className="sidebar-header">
        <div className="brand-row">
          <span className="brand-mark"><i className="bi bi-egg-fried"></i></span>
          <span className="brand-word fs-5 brand-word-text">Savora</span>
        </div>
        <i
          className={`bi ${collapsed ? 'bi-chevron-double-right' : 'bi-chevron-double-left'} d-none d-lg-block`}
          style={{ cursor: 'pointer', color: 'var(--text-faint)' }}
          onClick={() => setCollapsed(!collapsed)}
        />
      </div>

      <nav className="sidebar-nav">
        <div className="sidebar-section-title">Main</div>
        {navItems.map((item) => {
          if (!hasAccess(item.role)) return null;
          return (
            <Link
              key={item.route}
              href={`/dashboard/${item.route}`}
              className={`nav-link-app ${url.startsWith(`/dashboard/${item.route}`) ? 'active' : ''}`}
            >
              <i className={`bi ${item.icon}`}></i>
              <span className="sidebar-label">{item.label}</span>
            </Link>
          );
        })}
      </nav>

      <div className="sidebar-footer">
        <div className="user-chip" data-bs-toggle="dropdown">
          <span className="avatar-circle">{userInitials}</span>
          <div className="user-meta flex-grow-1 overflow-hidden">
            <div className="small fw-semibold text-truncate">{user?.name || 'User'}</div>
            <div className="text-faint text-truncate" style={{ fontSize: '.72rem' }}>
              {user?.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'Guest'}
            </div>
          </div>
          <i className="bi bi-chevron-expand text-faint sidebar-label"></i>
        </div>
      </div>
    </aside>
  );
}