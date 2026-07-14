import React from 'react';

interface KpiCardProps {
  label: string;
  value: string | number;
  icon: string;
  delta?: number | null;
  tone?: 'gold' | 'emerald' | 'rust' | 'slate';
}

export default function KpiCard({ label, value, icon, delta, tone = 'slate' }: KpiCardProps) {
  const toneColors = {
    gold: 'rgba(201,162,39,0.14)',
    emerald: 'rgba(63,166,114,0.14)',
    rust: 'rgba(193,80,61,0.14)',
    slate: 'rgba(108,122,137,0.14)',
  };

  const isPositive = delta !== null && delta !== undefined && delta >= 0;

  return (
    <div className="card-elev kpi-card h-100">
      <div className="d-flex align-items-start justify-content-between mb-3">
        <div
          className="kpi-icon"
          style={{ background: toneColors[tone], color: `var(--${tone})` }}
        >
          <i className={`bi ${icon}`}></i>
        </div>
        {delta !== null && delta !== undefined && (
          <span className={`delta-chip ${isPositive ? 'delta-up' : 'delta-down'}`}>
            <i className={`bi ${isPositive ? 'bi-arrow-up-short' : 'bi-arrow-down-short'}`}></i>
            {Math.abs(delta)}%
          </span>
        )}
      </div>
      <div className="kpi-value">{value}</div>
      <div className="text-muted-soft small mt-1">{label}</div>
    </div>
  );
}