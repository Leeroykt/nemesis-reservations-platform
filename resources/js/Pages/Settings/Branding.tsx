import React, { useState, useEffect, useRef } from 'react';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import SettingsSidebar from '@/Pages/Settings/SettingsSidebar';

interface BrandingData {
  primary_color_hex: string;
  logo_path: string | null;
}

export default function BrandingSettings() {
  const { data, loading, error, refetch } = useApi<BrandingData>('/settings/restaurant');
  const [form, setForm] = useState<Partial<BrandingData>>({});
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isUploading, setIsUploading] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (data) {
      setForm(data);
      if (data.logo_path) {
        setLogoPreview(data.logo_path);
      }
    }
  }, [data]);

  const handleColorChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const color = e.target.value;
    setForm(prev => ({ ...prev, primary_color_hex: color }));
    document.documentElement.style.setProperty('--gold', color);
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setIsUploading(true);
      setLogoFile(file);
      const reader = new FileReader();
      reader.onloadend = () => {
        setLogoPreview(reader.result as string);
        setIsUploading(false);
      };
      reader.onerror = () => {
        setIsUploading(false);
        setToast({ message: 'Failed to read file. Please try again.', type: 'error' });
      };
      reader.readAsDataURL(file);
    }
  };

  const removeLogo = () => {
    setLogoFile(null);
    setLogoPreview(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      const formData = new FormData();
      
      if (form.primary_color_hex) {
        formData.append('primary_color_hex', form.primary_color_hex);
      }

      if (logoFile) {
        formData.append('logo', logoFile);
      }

      await api.patch('/settings/branding', formData);
      setToast({ message: 'Branding updated!', type: 'success' });
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
          <SettingsSidebar active="branding" />
        </div>
        <div className="col-lg-9">
          <div className="card-elev p-4">
            <h5 className="fw-bold mb-3">Branding</h5>
            <p className="text-muted-soft small mb-4">Customize your restaurant's brand identity.</p>

            <form onSubmit={handleSubmit}>
              {/* Primary Color */}
              <div className="mb-4">
                <label className="form-label">Primary Color</label>
                <div className="d-flex align-items-center gap-3">
                  <input
                    type="color"
                    className="form-control form-control-color"
                    style={{ width: '60px', height: '50px', padding: '4px' }}
                    value={form.primary_color_hex || '#C9A227'}
                    onChange={handleColorChange}
                  />
                  <input
                    type="text"
                    className="form-control"
                    style={{ width: '140px' }}
                    value={form.primary_color_hex || '#C9A227'}
                    onChange={(e) => {
                      const value = e.target.value;
                      if (/^#[a-fA-F0-9]{6}$/.test(value) || value === '') {
                        setForm(prev => ({ ...prev, primary_color_hex: value }));
                        if (value) {
                          document.documentElement.style.setProperty('--gold', value);
                        }
                      }
                    }}
                    placeholder="#C9A227"
                  />
                  <span className="text-muted-soft small">(Hex color code)</span>
                </div>
                <div className="text-faint small mt-1">This color is used for buttons, accents, and highlights.</div>
              </div>

              {/* Logo Upload */}
              <div className="mb-4">
                <label className="form-label">Logo</label>
                <div className="d-flex align-items-center gap-4">
                  <div
                    className="d-flex align-items-center justify-content-center"
                    style={{
                      width: '120px',
                      height: '120px',
                      border: '2px dashed var(--border)',
                      borderRadius: '12px',
                      background: 'var(--surface-2)',
                      overflow: 'hidden',
                      position: 'relative',
                    }}
                  >
                    {isUploading ? (
                      <div className="d-flex flex-column align-items-center gap-2">
                        <div className="spinner-border text-gold" role="status" style={{ width: '2rem', height: '2rem' }}>
                          <span className="visually-hidden">Uploading...</span>
                        </div>
                        <span className="text-faint small">Uploading...</span>
                      </div>
                    ) : logoPreview ? (
                      <img
                        src={logoPreview}
                        alt="Restaurant Logo"
                        style={{ maxWidth: '100%', maxHeight: '100%', objectFit: 'contain' }}
                      />
                    ) : (
                      <span className="text-muted-soft small text-center">
                        <i className="bi bi-image" style={{ fontSize: '2rem', display: 'block' }}></i>
                        No logo
                      </span>
                    )}
                  </div>
                  <div>
                    <input
                      ref={fileInputRef}
                      type="file"
                      className="form-control"
                      accept="image/jpeg,image/png,image/svg+xml"
                      onChange={handleFileChange}
                      style={{ display: 'none' }}
                      id="logo-upload"
                      disabled={isSubmitting}
                    />
                    <label htmlFor="logo-upload" className={`btn btn-dark-ghost btn-sm ${isSubmitting ? 'opacity-50' : ''}`}>
                      <i className="bi bi-upload me-1"></i> Upload
                    </label>
                    {(logoPreview || data?.logo_path) && (
                      <button
                        type="button"
                        className="btn btn-outline-danger btn-sm ms-2"
                        onClick={removeLogo}
                        disabled={isSubmitting}
                      >
                        <i className="bi bi-trash"></i>
                      </button>
                    )}
                    <div className="text-faint small mt-2">
                      Recommended: SVG, PNG, or JPG. Max 2MB.
                    </div>
                    {isUploading && (
                      <div className="text-faint small mt-1 text-gold">
                        <i className="bi bi-arrow-repeat me-1"></i> Processing image...
                      </div>
                    )}
                  </div>
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