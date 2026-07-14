import React, { useState, useEffect } from 'react';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import SettingsSidebar from '@/Pages/Settings/SettingsSidebar';

interface EmailTemplate {
  id: number;
  key: string;
  name: string;
  subject: string;
  body: string;
}

type EditableEmailTemplateFields = Pick<EmailTemplate, 'subject' | 'body'>;

const templateDescriptions: Record<string, string> = {
  confirm: 'Sent when a guest makes a booking.',
  reminder: 'Sent as a reminder before the booking.',
  cancel: 'Sent when a booking is cancelled.',
  vip: 'Sent when a guest is upgraded to VIP status.',
};

const tokenHelp: Record<string, string[]> = {
  confirm: ['{{guest_name}}', '{{party_size}}', '{{date}}', '{{time}}', '{{booking_id}}', '{{restaurant_name}}'],
  reminder: ['{{guest_name}}', '{{party_size}}', '{{date}}', '{{time}}', '{{booking_id}}', '{{restaurant_name}}'],
  cancel: ['{{guest_name}}', '{{date}}', '{{time}}', '{{booking_id}}', '{{restaurant_name}}'],
  vip: ['{{guest_name}}', '{{restaurant_name}}'],
};

// ✅ FIXED: Default empty form state
const defaultEditForm: EditableEmailTemplateFields = {
  subject: '',
  body: '',
};

export default function EmailTemplatesSettings() {
  const { data, loading, error, refetch } = useApi<EmailTemplate[]>('/settings/email-templates');
  const [templates, setTemplates] = useState<EmailTemplate[]>([]);
  const [editingKey, setEditingKey] = useState<string | null>(null);
  const [editForm, setEditForm] = useState<EditableEmailTemplateFields>(defaultEditForm);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);
  const [validationErrors, setValidationErrors] = useState<{ subject?: string; body?: string }>({});

  useEffect(() => {
    if (data) {
      setTemplates(data);
    }
  }, [data]);

  const validateTokens = (text: string, allowedTokens: string[]): string[] => {
    const tokenRegex = /{{[^}]+}}/g;
    const foundTokens = text.match(tokenRegex) || [];
    const invalidTokens = foundTokens.filter(token => !allowedTokens.includes(token));
    return invalidTokens;
  };

  const startEditing = (template: EmailTemplate) => {
    setEditingKey(template.key);
    setEditForm({
      subject: template.subject,
      body: template.body,
    });
    setValidationErrors({});
  };

  // ✅ FIXED: Reset to default form, not empty object
  const cancelEditing = () => {
    setEditingKey(null);
    setEditForm(defaultEditForm);
    setValidationErrors({});
  };

  const handleChange = (field: keyof EditableEmailTemplateFields, value: string) => {
    setEditForm(prev => ({ ...prev, [field]: value }));
    
    if (validationErrors[field]) {
      setValidationErrors(prev => ({ ...prev, [field]: undefined }));
    }
  };

  const validateForm = (key: string): boolean => {
    const errors: { subject?: string; body?: string } = {};
    const allowedTokens = tokenHelp[key] || [];

    if (editForm.subject) {
      const invalidTokens = validateTokens(editForm.subject, allowedTokens);
      if (invalidTokens.length > 0) {
        errors.subject = `Invalid tokens: ${invalidTokens.join(', ')}. Allowed: ${allowedTokens.join(', ')}`;
      }
    }

    if (editForm.body) {
      const invalidTokens = validateTokens(editForm.body, allowedTokens);
      if (invalidTokens.length > 0) {
        errors.body = `Invalid tokens: ${invalidTokens.join(', ')}. Allowed: ${allowedTokens.join(', ')}`;
      }
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent, key: string) => {
    e.preventDefault();
    
    if (!validateForm(key)) {
      setToast({ 
        message: 'Please fix the token validation errors.', 
        type: 'error' 
      });
      return;
    }

    setIsSubmitting(true);

    try {
      await api.patch(`/settings/email-templates/${key}`, editForm);
      setToast({ message: 'Email template updated successfully!', type: 'success' });
      setEditingKey(null);
      setValidationErrors({});
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

  const renderTokens = (tokens: string[]) => {
    return tokens.map((token, index) => (
      <code key={token} className="text-gold me-1">
        {token}
        {index < tokens.length - 1 && ' '}
      </code>
    ));
  };

  return (
    <DashboardLayout>
      <div className="row g-4">
        <div className="col-lg-3">
          <SettingsSidebar active="restaurant" />
        </div>
        <div className="col-lg-9">
          <div className="card-elev p-4">
            <h5 className="fw-bold mb-3">Email Templates</h5>
            <p className="text-muted-soft small mb-4">
              Customize the email templates sent to guests.
              <br />
              Available tokens:{' '}
              {renderTokens(['{{guest_name}}', '{{party_size}}', '{{date}}', '{{time}}', '{{booking_id}}', '{{restaurant_name}}'])}
            </p>

            {templates.length === 0 ? (
              <div className="text-center text-muted-soft py-4">
                No email templates found. Please run the seeder.
              </div>
            ) : (
              <div className="row g-3">
                {templates.map((template) => (
                  <div key={template.key} className="col-12">
                    <div className="card-hairline p-4">
                      {editingKey === template.key ? (
                        <form onSubmit={(e) => handleSubmit(e, template.key)}>
                          <div className="d-flex justify-content-between align-items-start mb-3">
                            <div>
                              <h6 className="fw-bold mb-0">{template.name}</h6>
                              <span className="text-muted-soft small">{templateDescriptions[template.key]}</span>
                            </div>
                            <button
                              type="button"
                              className="btn btn-dark-ghost btn-sm"
                              onClick={cancelEditing}
                              disabled={isSubmitting}
                            >
                              Cancel
                            </button>
                          </div>

                          <div className="mb-3">
                            <label className="form-label">Subject</label>
                            <input
                              type="text"
                              className={`form-control ${validationErrors.subject ? 'is-invalid' : ''}`}
                              value={editForm.subject || ''}
                              onChange={(e) => handleChange('subject', e.target.value)}
                              maxLength={160}
                              required
                            />
                            {validationErrors.subject && (
                              <div className="invalid-feedback d-block">{validationErrors.subject}</div>
                            )}
                            <div className="text-faint small mt-1">
                              Max 160 characters. Available tokens:{' '}
                              {renderTokens(tokenHelp[template.key] || [])}
                            </div>
                          </div>

                          <div className="mb-3">
                            <label className="form-label">Body</label>
                            <textarea
                              className={`form-control ${validationErrors.body ? 'is-invalid' : ''}`}
                              rows={6}
                              value={editForm.body || ''}
                              onChange={(e) => handleChange('body', e.target.value)}
                              maxLength={5000}
                              required
                            />
                            {validationErrors.body && (
                              <div className="invalid-feedback d-block">{validationErrors.body}</div>
                            )}
                            <div className="text-faint small mt-1">
                              Max 5000 characters. Available tokens:{' '}
                              {renderTokens(tokenHelp[template.key] || [])}
                            </div>
                          </div>

                          <div className="d-flex justify-content-end">
                            <LoadingButton
                              type="submit"
                              isLoading={isSubmitting}
                              className="btn btn-gold btn-sm"
                            >
                              Save Changes
                            </LoadingButton>
                          </div>
                        </form>
                      ) : (
                        <div>
                          <div className="d-flex justify-content-between align-items-start mb-2">
                            <div>
                              <h6 className="fw-bold mb-0">{template.name}</h6>
                              <span className="text-muted-soft small">{templateDescriptions[template.key]}</span>
                            </div>
                            <button
                              className="btn btn-dark-ghost btn-sm"
                              onClick={() => startEditing(template)}
                            >
                              <i className="bi bi-pencil me-1"></i> Edit
                            </button>
                          </div>
                          <div className="mb-1">
                            <span className="text-muted-soft small">Subject:</span>
                            <span className="ms-2">{template.subject}</span>
                          </div>
                          <div>
                            <span className="text-muted-soft small">Body preview:</span>
                            <div
                              className="text-muted-soft small mt-1"
                              style={{
                                background: 'var(--surface-2)',
                                padding: '8px 12px',
                                borderRadius: '8px',
                                maxHeight: '60px',
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                whiteSpace: 'pre-wrap',
                              }}
                            >
                              {template.body.length > 100 ? template.body.substring(0, 100) + '...' : template.body}
                            </div>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}