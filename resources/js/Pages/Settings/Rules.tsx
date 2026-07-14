import React, { useState, useEffect } from 'react';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import SettingsSidebar from '@/Pages/Settings/SettingsSidebar';

interface Rules {
  max_party_size: number;
  slot_length_minutes: number;
  buffer_minutes: number;
  cancellation_window_hours: number;
  deposit_required_above: number | null;
  avg_spend_per_person: number;
}

export default function RulesSettings() {
  const { data, loading, error, refetch } = useApi<Rules>('/settings/rules');
  const [form, setForm] = useState<Partial<Rules>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  useEffect(() => {
    if (data) {
      setForm(data);
    }
  }, [data]);

  const handleChange = (field: keyof Rules, value: any) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const validateForm = (): boolean => {
    // ✅ FIXED: Properly handle undefined/null checks
    const depositValue = form.deposit_required_above;
    const maxPartySize = form.max_party_size;
    
    // Only validate if deposit_required_above is not null/undefined and max_party_size exists
    if (depositValue !== null && depositValue !== undefined && maxPartySize) {
      if (depositValue >= maxPartySize) {
        setToast({
          message: `Deposit required above (${depositValue}) must be less than max party size (${maxPartySize}).`,
          type: 'error'
        });
        return false;
      }
    }
    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    // Confirmation dialog before saving
    if (!confirm('Are you sure you want to update the booking rules? This will affect all future bookings.')) {
      return;
    }

    setIsSubmitting(true);

    try {
      await api.patch('/settings/rules', form);
      setToast({ message: 'Booking rules updated!', type: 'success' });
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

  // ✅ FIXED: Safe check for validation class
  const isDepositInvalid = 
    form.deposit_required_above !== null && 
    form.deposit_required_above !== undefined && 
    form.max_party_size !== undefined &&
    form.deposit_required_above >= form.max_party_size;

  return (
    <DashboardLayout>
      <div className="row g-4">
        <div className="col-lg-3">
          <SettingsSidebar active="rules" />
        </div>
        <div className="col-lg-9">
          <div className="card-elev p-4">
            <h5 className="fw-bold mb-3">Booking Rules</h5>
            <p className="text-muted-soft small mb-4">Configure your restaurant's booking policies.</p>

            <form onSubmit={handleSubmit}>
              <div className="row g-3">
                <div className="col-md-6">
                  <label className="form-label">Max Party Size *</label>
                  <input
                    type="number"
                    className="form-control"
                    value={form.max_party_size || 14}
                    onChange={(e) => handleChange('max_party_size', parseInt(e.target.value))}
                    min="1"
                    max="50"
                    required
                  />
                  <div className="text-faint small mt-1">Maximum number of guests per booking.</div>
                </div>
                <div className="col-md-6">
                  <label className="form-label">Slot Length (minutes) *</label>
                  <input
                    type="number"
                    className="form-control"
                    value={form.slot_length_minutes || 90}
                    onChange={(e) => handleChange('slot_length_minutes', parseInt(e.target.value))}
                    min="30"
                    max="180"
                    step="15"
                    required
                  />
                  <div className="text-faint small mt-1">Duration of each booking slot.</div>
                </div>
                <div className="col-md-6">
                  <label className="form-label">Buffer Time (minutes) *</label>
                  <input
                    type="number"
                    className="form-control"
                    value={form.buffer_minutes || 15}
                    onChange={(e) => handleChange('buffer_minutes', parseInt(e.target.value))}
                    min="0"
                    max="60"
                    step="5"
                    required
                  />
                  <div className="text-faint small mt-1">Time between bookings for cleaning.</div>
                </div>
                <div className="col-md-6">
                  <label className="form-label">Cancellation Window (hours) *</label>
                  <input
                    type="number"
                    className="form-control"
                    value={form.cancellation_window_hours || 4}
                    onChange={(e) => handleChange('cancellation_window_hours', parseInt(e.target.value))}
                    min="0"
                    max="48"
                    required
                  />
                  <div className="text-faint small mt-1">Hours before booking to cancel without penalty.</div>
                </div>
                <div className="col-md-6">
                  <label className="form-label">Deposit Required Above (party size)</label>
                  <input
                    type="number"
                    className={`form-control ${isDepositInvalid ? 'is-invalid' : ''}`}
                    value={form.deposit_required_above || ''}
                    onChange={(e) => handleChange('deposit_required_above', e.target.value ? parseInt(e.target.value) : null)}
                    min="0"
                    max="20"
                  />
                  {isDepositInvalid && (
                    <div className="invalid-feedback d-block">
                      Must be less than max party size ({form.max_party_size}).
                    </div>
                  )}
                  <div className="text-faint small mt-1">Leave empty for no deposit requirement. Must be less than max party size.</div>
                </div>
                <div className="col-md-6">
                  <label className="form-label">Average Spend Per Person ($) *</label>
                  <input
                    type="number"
                    className="form-control"
                    value={form.avg_spend_per_person || 25}
                    onChange={(e) => handleChange('avg_spend_per_person', parseFloat(e.target.value))}
                    min="0"
                    step="0.01"
                    required
                  />
                  <div className="text-faint small mt-1">Used for revenue calculations.</div>
                </div>
              </div>

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