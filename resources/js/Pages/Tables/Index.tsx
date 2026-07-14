/**
 * Tables Page – Floor plan & grid view with live status updates
 * Ref: 02-FEATURE-SPEC.md §5, 08-UI-DESIGN-SYSTEM.md (floor-plan)
 */

import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import StatusBadge from '@/Components/Common/StatusBadge';
import Toast from '@/Components/Common/Toast';

// ---------- Types ----------
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

type ViewMode = 'floor' | 'grid';

// ---------- Main Component ----------
export default function Tables() {
  const [viewMode, setViewMode] = useState<ViewMode>('floor');
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' | 'info' } | null>(null);

  // Fetch tables
  const { data, loading, error, refetch } = useApi<{ data: Table[] }>('/tables');

  const tables = data?.data || [];

  // Toast helper
  const showToast = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  // Update table status
  const updateStatus = async (tableId: number, newStatus: Table['status']) => {
    try {
      await api.patch(`/tables/${tableId}/status`, { status: newStatus });
      showToast(`Table status updated to ${newStatus}`);
      refetch();
    } catch (err: any) {
      showToast(err.message || 'Failed to update status', 'error');
    }
  };

  // Counts
  const total = tables.length;
  const available = tables.filter(t => t.status === 'Available').length;
  const occupied = tables.filter(t => t.status === 'Occupied').length;
  const reserved = tables.filter(t => t.status === 'Reserved').length;
  const cleaning = tables.filter(t => t.status === 'Cleaning').length;

  return (
    <DashboardLayout>
      {/* Header */}
      <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h3 className="fw-bold mb-1">Tables</h3>
          <p className="text-muted-soft mb-0 small">
            {total} tables · {available} available · {occupied} occupied · {reserved} reserved · {cleaning} cleaning
          </p>
        </div>
        <div className="d-flex flex-wrap gap-2 align-items-center">
          <div className="btn-group" role="group">
            <button
              className={`btn btn-sm ${viewMode === 'floor' ? 'btn-gold' : 'btn-dark-ghost'}`}
              onClick={() => setViewMode('floor')}
            >
              <i className="bi bi-diagram-3 me-1"></i>Floor
            </button>
            <button
              className={`btn btn-sm ${viewMode === 'grid' ? 'btn-gold' : 'btn-dark-ghost'}`}
              onClick={() => setViewMode('grid')}
            >
              <i className="bi bi-grid me-1"></i>Grid
            </button>
          </div>
          <button
            className="btn btn-dark-ghost btn-sm"
            onClick={() => refetch()}
            title="Refresh"
          >
            <i className="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>

      {/* Content */}
      {loading ? (
        <div className="text-center py-5">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      ) : error ? (
        <div className="text-center text-rust py-5">
          <i className="bi bi-exclamation-triangle me-2"></i>
          {error}
        </div>
      ) : tables.length === 0 ? (
        <div className="card-elev p-4 text-center text-muted-soft">
          No tables defined for this restaurant.
        </div>
      ) : viewMode === 'floor' ? (
        <FloorPlan tables={tables} onUpdateStatus={updateStatus} />
      ) : (
        <GridView tables={tables} onUpdateStatus={updateStatus} />
      )}

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}

// ---------- FloorPlan Subcomponent ----------
interface FloorPlanProps {
  tables: Table[];
  onUpdateStatus: (tableId: number, status: Table['status']) => void;
}

function FloorPlan({ tables, onUpdateStatus }: FloorPlanProps) {
  const [updatingId, setUpdatingId] = useState<number | null>(null);

  const handleStatusChange = async (tableId: number, newStatus: Table['status']) => {
    setUpdatingId(tableId);
    await onUpdateStatus(tableId, newStatus);
    setUpdatingId(null);
  };

  return (
    <div className="card-elev p-3">
      <div className="floor-plan">
        {tables.map((table) => {
          const statusClass = 
            table.status === 'Available' ? 'st-available' :
            table.status === 'Occupied' ? 'st-occupied' :
            table.status === 'Reserved' ? 'st-reserved' :
            'st-cleaning';

          const shapeClass = table.shape === 'round' ? 'round' : '';

          return (
            <div
              key={table.id}
              className={`floor-table ${shapeClass} ${statusClass}`}
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
                  {(['Available', 'Occupied', 'Reserved', 'Cleaning'] as Table['status'][]).map((status) => (
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
    </div>
  );
}

// ---------- GridView Subcomponent ----------
interface GridViewProps {
  tables: Table[];
  onUpdateStatus: (tableId: number, status: Table['status']) => void;
}

function GridView({ tables, onUpdateStatus }: GridViewProps) {
  const [updatingId, setUpdatingId] = useState<number | null>(null);

  const handleStatusChange = async (tableId: number, newStatus: Table['status']) => {
    setUpdatingId(tableId);
    await onUpdateStatus(tableId, newStatus);
    setUpdatingId(null);
  };

  return (
    <div className="card-elev">
      <div className="table-responsive">
        <table className="table-app">
          <thead>
            <tr>
              <th>Code</th>
              <th>Zone</th>
              <th>Capacity</th>
              <th>Shape</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {tables.map((table) => (
              <tr key={table.id}>
                <td><strong>{table.code}</strong></td>
                <td>{table.zone || '—'}</td>
                <td>{table.capacity}</td>
                <td>{table.shape}</td>
                <td>
                  <StatusBadge status={table.status} />
                </td>
                <td>
                  <div className="dropdown">
                    <button
                      className="btn btn-dark-ghost btn-sm"
                      data-bs-toggle="dropdown"
                      disabled={updatingId === table.id}
                    >
                      {updatingId === table.id ? (
                        <span className="spinner-border spinner-border-sm" role="status" />
                      ) : (
                        'Update'
                      )}
                    </button>
                    <ul className="dropdown-menu dropdown-menu-end shadow">
                      {(['Available', 'Occupied', 'Reserved', 'Cleaning'] as Table['status'][]).map((status) => (
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
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}