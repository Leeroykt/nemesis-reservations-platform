import React from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

interface RoleGateProps {
  children: React.ReactNode;
  allowedRoles: string[];
  fallback?: React.ReactNode;
}

export default function RoleGate({ children, allowedRoles, fallback = null }: RoleGateProps) {
  const { user } = usePage<PageProps>().props;
  const userRole = user?.role || 'guest';

  if (allowedRoles.includes(userRole)) {
    return <>{children}</>;
  }

  return <>{fallback}</>;
}