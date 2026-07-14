import React, { useState, useEffect } from 'react';

interface ToastProps {
  message: string;
  type?: 'success' | 'error' | 'info';
  duration?: number;
  onClose?: () => void;
}

export default function Toast({ message, type = 'success', duration = 3000, onClose }: ToastProps) {
  const [visible, setVisible] = useState(true);

  useEffect(() => {
    const timer = setTimeout(() => {
      setVisible(false);
      if (onClose) onClose();
    }, duration);

    return () => clearTimeout(timer);
  }, [duration, onClose]);

  const handleClose = () => {
    setVisible(false);
    // onClose is called when user manually closes
    if (onClose) onClose();
  };

  if (!visible) return null;

  const iconMap = {
    success: 'bi-check-circle-fill text-emerald',
    error: 'bi-x-circle-fill text-rust',
    info: 'bi-info-circle-fill text-gold',
  };

  return (
    <div className="toast-container position-fixed bottom-0 end-0 p-4" style={{ zIndex: 1200 }}>
      <div className="toast show" role="alert" style={{ background: 'var(--surface)', border: '1px solid var(--border-strong)', borderRadius: '14px' }}>
        <div className="d-flex align-items-center p-3">
          <i className={`bi ${iconMap[type]} me-2`}></i>
          <div className="me-auto small fw-semibold">{message}</div>
          <button type="button" className="btn-close" onClick={handleClose}></button>
        </div>
      </div>
    </div>
  );
}