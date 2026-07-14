import React, { useState, useEffect } from 'react';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import SettingsSidebar from '@/Pages/Settings/SettingsSidebar';

interface Hour {
  id: number;
  day_of_week: number;
  open_time: string | null;
  close_time: string | null;
  is_closed: boolean;
}

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

export default function HoursSettings() {
  const { data, loading, error, refetch } = useApi<Hour[]>('/settings/hours');
  const [hours, setHours] = useState<Hour[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  useEffect(() => {
    if (data) {
      setHours(data);
    }
  }, [data]);

  const handleChange = (index: number, field: keyof Hour, value: any) => {
    const updated = [...hours];
    updated[index] = { ...updated[index], [field]: value };
    setHours(updated);
  };

  const validateHours = (): boolean => {
    for (const hour of hours) {
      if (!hour.is_closed && hour.open_time && hour.close_time) {
        if (hour.open_time >= hour.close_time) {
          setToast({
            message: `${DAYS[hour.day_of_week]}: Open time must be before close time.`,
            type: 'error'
          });
          return false;
        }
      }
    }
    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateHours()) {
      return;
    }

    setIsSubmitting(true);

    try {
      await api.patch('/settings/hours', { hours });
      setToast({ message: 'Opening hours updated!', type: 'success' });
      refetch();
    } catch (err: any) {
      setToast({ message: err.message || 'Update failed', type: 'error' });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="d-flex justify-content-center align-items-center" style={{ height: '400px' }}>
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="row g-4">
        <div className="col-lg-3">
          <SettingsSidebar active="hours" />
        </div>
        <div className="col-lg-9">
          <div className="card-elev p-4">
            <h5 className="fw-bold mb-3">Opening Hours</h5>
            <p className="text-muted-soft small mb-4">Set your restaurant's operating hours for each day.</p>

            <form onSubmit={handleSubmit}>
              {hours.map((hour, index) => (
                <div key={hour.day_of_week} className="hours-row">
                  <div className="d-flex align-items-center gap-3 flex-wrap">
                    <div style={{ width: '100px' }}>
                      <span className="fw-semibold">{DAYS[hour.day_of_week]}</span>
                    </div>
                    <div className="form-check">
                      <input
                        type="checkbox"
                        className="form-check-input"
                        id={`closed-${hour.day_of_week}`}
                        checked={hour.is_closed}
                        onChange={(e) => handleChange(index, 'is_closed', e.target.checked)}
                      />
                      <label className="form-check-label small" htmlFor={`closed-${hour.day_of_week}`}>
                        Closed
                      </label>
                    </div>
                    {!hour.is_closed && (
                      <>
                        <input
                          type="time"
                          className="form-control"
                          style={{ width: '130px' }}
                          value={hour.open_time || ''}
                          onChange={(e) => handleChange(index, 'open_time', e.target.value)}
                          required={!hour.is_closed}
                        />
                        <span className="text-muted-soft small">to</span>
                        <input
                          type="time"
                          className="form-control"
                          style={{ width: '130px' }}
                          value={hour.close_time || ''}
                          onChange={(e) => handleChange(index, 'close_time', e.target.value)}
                          required={!hour.is_closed}
                        />
                      </>
                    )}
                  </div>
                </div>
              ))}

              <div className="mt-4 d-flex justify-content-end">
                <LoadingButton
                  type="submit"
                  isLoading={isSubmitting}
                  className="btn btn-gold"
                >
                  Save Changes
                </LoadingButton>
              </div>
            </form>
          </div>
        </div>
      </div>

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}