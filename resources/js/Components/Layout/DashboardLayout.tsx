import React, { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import Sidebar from './Sidebar';
import Topbar from './Topbar';

interface DashboardLayoutProps {
  children: ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
  const { user } = usePage<PageProps>().props;

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