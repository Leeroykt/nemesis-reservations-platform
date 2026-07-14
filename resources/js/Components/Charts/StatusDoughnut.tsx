import React, { useEffect, useRef } from 'react';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

interface StatusDoughnutProps {
  labels: string[];
  values: number[];
}

export default function StatusDoughnut({ labels, values }: StatusDoughnutProps) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const chartInstance = useRef<Chart | null>(null);

  useEffect(() => {
    if (!canvasRef.current) return;
    const ctx = canvasRef.current.getContext('2d');
    if (!ctx) return;

    chartInstance.current = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [
          {
            data: values,
            backgroundColor: ['var(--emerald)', 'var(--gold)', 'var(--slate)', 'var(--rust)'],
            borderColor: 'var(--surface)',
            borderWidth: 3,
            hoverOffset: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: 'var(--text)',
              boxWidth: 10,
              boxHeight: 10,
              usePointStyle: true,
              pointStyle: 'circle',
              font: { family: 'Raleway', size: 11, weight: 'bold' },
              padding: 14,
            },
          },
          tooltip: {
            backgroundColor: 'var(--surface)',
            titleColor: 'var(--text)',
            bodyColor: 'var(--text)',
            borderColor: 'var(--border)',
            borderWidth: 1,
          },
        },
      },
    });

    return () => {
      chartInstance.current?.destroy();
    };
  }, [labels, values]);

  return (
    <div className="chart-wrap" style={{ height: '280px' }}>
      <canvas ref={canvasRef} />
    </div>
  );
}