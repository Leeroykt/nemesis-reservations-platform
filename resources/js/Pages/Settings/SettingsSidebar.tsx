import React from 'react';
import { Link } from '@inertiajs/react';

interface SettingsSidebarProps {
  active: 'restaurant' | 'hours' | 'rules' | 'branding' | 'email-templates' | 'users';
}

export default function SettingsSidebar({ active }: SettingsSidebarProps) {
  const items = [
    { key: 'restaurant', label: 'Restaurant Info', icon: 'bi-building' },
    { key: 'hours', label: 'Opening Hours', icon: 'bi-clock' },
    { key: 'rules', label: 'Booking Rules', icon: 'bi-sliders' },
    { key: 'branding', label: 'Branding', icon: 'bi-palette' },
    { key: 'email-templates', label: 'Email Templates', icon: 'bi-envelope' },
    { key: 'users', label: 'Staff Management', icon: 'bi-people' },
  ];

  return (
    <div className="card-elev p-3">
      <h6 className="fw-bold mb-3">Settings</h6>
      <nav className="settings-nav">
        {items.map((item) => (
          <Link
            key={item.key}
            href={`/dashboard/settings/${item.key}`}
            className={`nav-link ${active === item.key ? 'active' : ''}`}
          >
            <i className={`bi ${item.icon} me-2`}></i>
            {item.label}
          </Link>
        ))}
      </nav>
    </div>
  );
}