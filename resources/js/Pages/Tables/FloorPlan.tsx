import React, { useState } from 'react';
import { api } from '@/lib/api';
import Toast from '@/Components/Common/Toast';

interface Table {
  id: number;
  code: string;
  zone: string | null;
  capacity: number;
  shape: 'round' | 'square' | 'rect';
  pos_x: number;
  pos_y: number;
  status: 'Available' | 'Occupied' | 'Reserved' | 'Cleaning';
}

interface FloorPlanProps {
  tables: Table[];
  onUpdateStatus: (tableId: number, status: Table['status']) => void;
}

export default function FloorPlan({ tables, onUpdateStatus }: FloorPlanProps) {
  const [updatingId, setUpdatingId] = useState<number | null>(null);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  const handleStatusChange = async (tableId: number, newStatus: Table['status']) => {
    setUpdatingId(tableId);
    try {
      await onUpdateStatus(tableId, newStatus);
      setToast({ message: `Table status updated to ${newStatus}`, type: 'success' });
    } catch (err: any) {
      setToast({ message: err.message || 'Update failed', type: 'error' });
    } finally {
      setUpdatingId(null);
    }
  };

  const statusClass = (status: Table['status']) => {
    switch (status) {
      case 'Available': return 'st-available';
      case 'Occupied': return 'st-occupied';
      case 'Reserved': return 'st-reserved';
      case 'Cleaning': return 'st-cleaning';
    }
  };

  const statusOptions: Table['status'][] = ['Available', 'Occupied', 'Reserved', 'Cleaning'];

  return (
    <div className="card-elev p-3">
      <div className="floor-plan">
        {tables.map((table) => {
          const shapeClass = table.shape === 'round' ? 'round' : '';
          return (
            <div
              key={table.id}
              className={`floor-table ${shapeClass} ${statusClass(table.status)}`}
              style={{
                left: `${table.pos_x}%`,
                top: `${table.pos_y}%`,
                minWidth: '60px',
                minHeight: '44px',
              }}
            >
              <div style={{ fontSize: '.85rem', fontWeight: 'bold' }}>{table.code}</div>
              <div className="cap">{table.capacity} guests</div>
              <div
                className="dropdown"
                onClick={(e) => e.stopPropagation()}
                style={{ marginTop: '4px' }}
              >
                <button
                  className="btn btn-dark-ghost btn-sm"
                  style={{ padding: '2px 8px', fontSize: '.65rem' }}
                  data-bs-toggle="dropdown"
                  disabled={updatingId === table.id}
                >
                  {updatingId === table.id ? (
                    <span className="spinner-border spinner-border-sm" role="status" />
                  ) : (
                    table.status
                  )}
                </button>
                <ul className="dropdown-menu dropdown-menu-end shadow">
                  {statusOptions.map((status) => (
                    <li key={status}>
                      <button
                        className="dropdown-item"
                        onClick={() => handleStatusChange(table.id, status)}
                      >
                        {status}
                      </button>
                    </li>
                  ))}
                </ul>
              </div>
            </div>
          );
        })}
      </div>
      <div className="d-flex flex-wrap gap-3 mt-3 text-muted-soft small">
        <span><span className="status-dot available" style={{ marginRight: '4px' }}></span>Available</span>
        <span><span className="status-dot occupied" style={{ marginRight: '4px' }}></span>Occupied</span>
        <span><span className="status-dot reserved" style={{ marginRight: '4px' }}></span>Reserved</span>
        <span><span className="status-dot cleaning" style={{ marginRight: '4px' }}></span>Cleaning</span>
      </div>
      {toast && <Toast message={toast.message} type={toast.type} />}
    </div>
  );
}