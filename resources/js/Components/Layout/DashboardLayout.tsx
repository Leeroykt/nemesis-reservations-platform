import React, { ReactNode } from 'react';
import Sidebar from './Sidebar';
import Topbar from './Topbar';
import { usePage } from '@inertiajs/react';

interface DashboardLayoutProps {
  children: ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
  const { user } = usePage().props;

  return (
    <div className="app-shell">
      <Sidebar user={user} />
      <div className="main-col">
        <Topbar user={user} />
        <main className="page-wrap fade-in">
          {children}
        </main>
      </div>
    </div>
  );
}