import React, { useEffect, useRef } from 'react';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

interface RevenueChartProps {
  labels: string[];
  thisWeek: number[];
  lastWeek: number[];
}

export default function RevenueChart({ labels, thisWeek, lastWeek }: RevenueChartProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const chartInstance = useRef<Chart | null>(null);

  useEffect(() => {
    if (!canvasRef.current) return;

    const ctx = canvasRef.current.getContext('2d');
    if (!ctx) return;

    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(201,162,39,0.28)');
    gradient.addColorStop(1, 'rgba(201,162,39,0)');

    chartInstance.current = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'This week',
            data: thisWeek,
            borderColor: 'var(--gold)',
            backgroundColor: gradient,
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: 'var(--gold)',
          },
          {
            label: 'Last week',
            data: lastWeek,
            borderColor: 'var(--slate)',
            borderDash: [5, 5],
            backgroundColor: 'transparent',
            borderWidth: 2,
            tension: 0.4,
            fill: false,
            pointRadius: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            position: 'top',
            align: 'end',
            labels: {
              color: 'var(--text-muted)',
              boxWidth: 10,
              boxHeight: 10,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { family: 'Raleway', size: 11, weight: 'bold' },
            },
          },
          tooltip: {
            backgroundColor: 'var(--surface)',
            titleColor: 'var(--text)',
            bodyColor: 'var(--text)',
            borderColor: 'var(--border)',
            borderWidth: 1,
            padding: 10,
            callbacks: {
              label: (ctx) => {
                const value = ctx.parsed.y;
                return value !== null && value !== undefined ? ` $${value.toLocaleString()}` : ' $0';
              },
            },
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              color: 'var(--text-muted)',
              font: { family: 'Raleway', size: 11, weight: 'bold' },
            },
          },
          y: {
            grid: { color: 'var(--border)' },
            ticks: {
              color: 'var(--text-muted)',
              font: { family: 'Raleway', size: 11, weight: 'bold' },
              callback: (value) => '$' + value,
            },
          },
        },
      },
    });

    return () => {
      chartInstance.current?.destroy();
    };
  }, [labels, thisWeek, lastWeek]);

  return (
    <div className="chart-wrap" style={{ height: '280px' }}>
      <canvas ref={canvasRef} />
    </div>
  );
}