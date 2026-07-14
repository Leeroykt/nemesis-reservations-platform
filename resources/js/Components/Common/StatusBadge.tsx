import React from 'react';

interface StatusBadgeProps {
  status: string;
}

export default function StatusBadge({ status }: StatusBadgeProps) {
  const statusKey = status.toLowerCase();
  return (
    <span className={`badge-status ${statusKey}`}>
      <span className={`status-dot ${statusKey}`}></span>
      {status}
    </span>
  );
}