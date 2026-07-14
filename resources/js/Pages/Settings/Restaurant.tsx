import React, { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import SettingsSidebar from '@/Pages/Settings/SettingsSidebar';

interface RestaurantData {
  id: number;
  name: string;
  tagline: string | null;
  email: string | null;
  phone: string | null;
  address: string | null;
  timezone: string;
  currency: string;
  primary_color_hex: string;
  logo_path: string | null;
}

export default function RestaurantSettings() {
  const { data, loading, error, refetch } = useApi<RestaurantData>('/settings/restaurant');
  const [form, setForm] = useState<Partial<RestaurantData>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  useEffect(() => {
    if (data) {
      setForm(data);
    }
  }, [data]);

  const handleChange = (field: keyof RestaurantData, value: string) => {
    setForm(prev => ({ ...prev, [field]: value }));
  };

  const validateForm = (): boolean => {
    // Email validation
    if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
      setToast({ message: 'Please enter a valid email address.', type: 'error' });
      return false;
    }
    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);

    try {
      await api.patch('/settings/restaurant', form);
      setToast({ message: 'Restaurant information updated!', type: 'success' });
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
          <SettingsSidebar active="restaurant" />
        </div>
        <div className="col-lg-9">
          <div className="card-elev p-4">
            <h5 className="fw-bold mb-3">Restaurant Information</h5>
            <p className="text-muted-soft small mb-4">Update your restaurant's basic details.</p>

            <form onSubmit={handleSubmit}>
              <div className="row g-3">
                <div className="col-md-6">
                  <label className="form-label">Restaurant Name *</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.name || ''}
                    onChange={(e) => handleChange('name', e.target.value)}
                    required
                  />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Tagline</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.tagline || ''}
                    onChange={(e) => handleChange('tagline', e.target.value)}
                    placeholder="Fine Dining & Wine Bar"
                  />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Email</label>
                  <input
                    type="email"
                    className="form-control"
                    value={form.email || ''}
                    onChange={(e) => handleChange('email', e.target.value)}
                    placeholder="restaurant@example.com"
                  />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Phone</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.phone || ''}
                    onChange={(e) => handleChange('phone', e.target.value)}
                    placeholder="+263 24 270 1234"
                  />
                </div>
                <div className="col-12">
                  <label className="form-label">Address</label>
                  <input
                    type="text"
                    className="form-control"
                    value={form.address || ''}
                    onChange={(e) => handleChange('address', e.target.value)}
                    placeholder="123 Main St, City"
                  />
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