import React, { useState } from 'react';
import DashboardLayout from '@/Components/Layout/DashboardLayout';
import { useApi } from '@/hooks/useApi';
import { api } from '@/lib/api';
import LoadingButton from '@/Components/Common/LoadingButton';
import Toast from '@/Components/Common/Toast';
import SettingsSidebar from '@/Pages/Settings/SettingsSidebar';

interface User {
  id: number;
  name: string;
  email: string;
  role: 'owner' | 'manager' | 'host';
  avatar_initials: string;
  created_at: string;
}

interface Meta {
  total: number;
  page: number;
  perPage: number;
  hasMore: boolean;
}

const roleLabels = {
  owner: 'Owner',
  manager: 'Manager',
  host: 'Host',
};

export default function UsersSettings() {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  // Form state
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    role: 'host' as 'owner' | 'manager' | 'host',
  });
  const [formErrors, setFormErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Fetch users
  const { data, loading, error, refetch } = useApi<{ data: User[]; meta: Meta }>(
    '/users',
    { search, page, per_page: 15 }
  );

  const users = data?.data || [];
  const meta = data?.meta || { total: 0, page: 1, perPage: 15, hasMore: false };

  const showToast = (message: string, type: 'success' | 'error' = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  const resetForm = () => {
    setForm({ name: '', email: '', password: '', role: 'host' });
    setFormErrors({});
    setIsSubmitting(false);
  };

  const openCreateModal = () => {
    resetForm();
    setIsCreateModalOpen(true);
  };

  const openEditModal = (user: User) => {
    setSelectedUser(user);
    setForm({
      name: user.name,
      email: user.email,
      password: '',
      role: user.role,
    });
    setFormErrors({});
    setIsEditModalOpen(true);
  };

  const openDeleteModal = (user: User) => {
    setSelectedUser(user);
    setIsDeleteModalOpen(true);
  };

  const closeModals = () => {
    setIsCreateModalOpen(false);
    setIsEditModalOpen(false);
    setIsDeleteModalOpen(false);
    setSelectedUser(null);
    resetForm();
  };

  // Create user
  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setFormErrors({});

    try {
      await api.post('/users', form);
      showToast('Staff account created successfully!');
      closeModals();
      refetch();
    } catch (err: any) {
      if (err.errors) {
        setFormErrors(err.errors);
      } else {
        showToast(err.message || 'Creation failed', 'error');
      }
      setIsSubmitting(false);
    }
  };

  // Update user
  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedUser) return;

    setIsSubmitting(true);
    setFormErrors({});

    try {
      await api.patch(`/users/${selectedUser.id}`, form);
      showToast('Staff account updated successfully!');
      closeModals();
      refetch();
    } catch (err: any) {
      if (err.errors) {
        setFormErrors(err.errors);
      } else {
        showToast(err.message || 'Update failed', 'error');
      }
      setIsSubmitting(false);
    }
  };

  // Delete user
  const handleDelete = async () => {
    if (!selectedUser) return;

    try {
      await api.delete(`/users/${selectedUser.id}`);
      showToast('Staff account deleted successfully!');
      closeModals();
      refetch();
    } catch (err: any) {
      showToast(err.message || 'Delete failed', 'error');
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
          <SettingsSidebar active="users" />
        </div>
        <div className="col-lg-9">
          <div className="card-elev p-4">
            <div className="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
              <div>
                <h5 className="fw-bold mb-1">Staff Management</h5>
                <p className="text-muted-soft small mb-0">Manage your restaurant staff accounts.</p>
              </div>
              <button className="btn btn-gold btn-sm" onClick={openCreateModal}>
                <i className="bi bi-plus-lg me-1"></i> Add Staff
              </button>
            </div>

            {/* Search */}
            <div className="mb-3">
              <div className="topbar-search" style={{ maxWidth: '300px' }}>
                <i className="bi bi-search"></i>
                <input
                  type="text"
                  placeholder="Search staff..."
                  value={search}
                  onChange={(e) => { setSearch(e.target.value); setPage(1); }}
                />
              </div>
            </div>

            {/* Table */}
            <div className="table-responsive">
              <table className="table-app">
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  {users.length === 0 ? (
                    <tr>
                      <td colSpan={5} className="text-center text-muted-soft py-4">
                        No staff accounts found.
                      </td>
                    </tr>
                  ) : (
                    users.map((user) => (
                      <tr key={user.id}>
                        <td>
                          <div className="d-flex align-items-center gap-2">
                            <span className="avatar-circle" style={{ width: '32px', height: '32px', fontSize: '0.7rem' }}>
                              {user.avatar_initials || user.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()}
                            </span>
                            <span className="fw-semibold">{user.name}</span>
                          </div>
                        </td>
                        <td>{user.email}</td>
                        <td>
                          <span className={`badge-status ${user.role}`}>
                            <span className={`status-dot ${user.role}`}></span>
                            {roleLabels[user.role]}
                          </span>
                        </td>
                        <td className="text-muted-soft small">
                          {new Date(user.created_at).toLocaleDateString()}
                        </td>
                        <td className="text-end">
                          <div className="dropdown">
                            <button
                              className="icon-btn"
                              data-bs-toggle="dropdown"
                              onClick={(e) => e.stopPropagation()}
                            >
                              <i className="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul className="dropdown-menu dropdown-menu-end shadow">
                              <li>
                                <button className="dropdown-item" onClick={() => openEditModal(user)}>
                                  <i className="bi bi-pencil me-2"></i> Edit
                                </button>
                              </li>
                              <li>
                                <button className="dropdown-item text-danger" onClick={() => openDeleteModal(user)}>
                                  <i className="bi bi-trash me-2"></i> Delete
                                </button>
                              </li>
                            </ul>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            <div className="d-flex align-items-center justify-content-between mt-3">
              <span className="text-muted-soft small">
                Showing {users.length} of {meta.total} staff
              </span>
              <div className="d-flex gap-2">
                <button
                  className="btn btn-dark-ghost btn-sm"
                  disabled={meta.page <= 1}
                  onClick={() => setPage(meta.page - 1)}
                >
                  Previous
                </button>
                <span className="d-flex align-items-center small text-muted-soft">
                  Page {meta.page} of {Math.ceil(meta.total / meta.perPage)}
                </span>
                <button
                  className="btn btn-dark-ghost btn-sm"
                  disabled={!meta.hasMore}
                  onClick={() => setPage(meta.page + 1)}
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ============================================================ */}
      {/* CREATE MODAL */}
      {/* ============================================================ */}
      <div
        className={`modal fade ${isCreateModalOpen ? 'show d-block' : ''}`}
        style={{ display: isCreateModalOpen ? 'block' : 'none', background: isCreateModalOpen ? 'rgba(0,0,0,0.5)' : 'none' }}
        onClick={(e) => { if (e.target === e.currentTarget) closeModals(); }}
      >
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title fw-bold">Add Staff Account</h5>
              <button type="button" className="btn-close" onClick={closeModals}></button>
            </div>
            <form onSubmit={handleCreate}>
              <div className="modal-body">
                <div className="mb-3">
                  <label className="form-label">Full Name *</label>
                  <input
                    type="text"
                    className={`form-control ${formErrors.name ? 'is-invalid' : ''}`}
                    value={form.name}
                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                    required
                  />
                  {formErrors.name && <div className="invalid-feedback d-block">{formErrors.name[0]}</div>}
                </div>
                <div className="mb-3">
                  <label className="form-label">Email *</label>
                  <input
                    type="email"
                    className={`form-control ${formErrors.email ? 'is-invalid' : ''}`}
                    value={form.email}
                    onChange={(e) => setForm({ ...form, email: e.target.value })}
                    required
                  />
                  {formErrors.email && <div className="invalid-feedback d-block">{formErrors.email[0]}</div>}
                </div>
                <div className="mb-3">
                  <label className="form-label">Password *</label>
                  <input
                    type="password"
                    className={`form-control ${formErrors.password ? 'is-invalid' : ''}`}
                    value={form.password}
                    onChange={(e) => setForm({ ...form, password: e.target.value })}
                    required
                  />
                  {formErrors.password && <div className="invalid-feedback d-block">{formErrors.password[0]}</div>}
                  <div className="text-faint small mt-1">Minimum 8 characters.</div>
                </div>
                <div className="mb-3">
                  <label className="form-label">Role *</label>
                  <select
                    className={`form-select ${formErrors.role ? 'is-invalid' : ''}`}
                    value={form.role}
                    onChange={(e) => setForm({ ...form, role: e.target.value as any })}
                    required
                  >
                    <option value="host">Host</option>
                    <option value="manager">Manager</option>
                    <option value="owner">Owner</option>
                  </select>
                  {formErrors.role && <div className="invalid-feedback d-block">{formErrors.role[0]}</div>}
                </div>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-dark-ghost btn-sm" onClick={closeModals}>Cancel</button>
                <LoadingButton type="submit" isLoading={isSubmitting} className="btn btn-gold btn-sm">
                  Create Account
                </LoadingButton>
              </div>
            </form>
          </div>
        </div>
      </div>

      {/* ============================================================ */}
      {/* EDIT MODAL */}
      {/* ============================================================ */}
      <div
        className={`modal fade ${isEditModalOpen ? 'show d-block' : ''}`}
        style={{ display: isEditModalOpen ? 'block' : 'none', background: isEditModalOpen ? 'rgba(0,0,0,0.5)' : 'none' }}
        onClick={(e) => { if (e.target === e.currentTarget) closeModals(); }}
      >
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title fw-bold">Edit Staff Account</h5>
              <button type="button" className="btn-close" onClick={closeModals}></button>
            </div>
            <form onSubmit={handleUpdate}>
              <div className="modal-body">
                <div className="mb-3">
                  <label className="form-label">Full Name</label>
                  <input
                    type="text"
                    className={`form-control ${formErrors.name ? 'is-invalid' : ''}`}
                    value={form.name}
                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                  />
                  {formErrors.name && <div className="invalid-feedback d-block">{formErrors.name[0]}</div>}
                </div>
                <div className="mb-3">
                  <label className="form-label">Email</label>
                  <input
                    type="email"
                    className={`form-control ${formErrors.email ? 'is-invalid' : ''}`}
                    value={form.email}
                    onChange={(e) => setForm({ ...form, email: e.target.value })}
                  />
                  {formErrors.email && <div className="invalid-feedback d-block">{formErrors.email[0]}</div>}
                </div>
                <div className="mb-3">
                  <label className="form-label">New Password</label>
                  <input
                    type="password"
                    className={`form-control ${formErrors.password ? 'is-invalid' : ''}`}
                    value={form.password}
                    onChange={(e) => setForm({ ...form, password: e.target.value })}
                    placeholder="Leave blank to keep current password"
                  />
                  {formErrors.password && <div className="invalid-feedback d-block">{formErrors.password[0]}</div>}
                  <div className="text-faint small mt-1">Minimum 8 characters. Leave blank to keep current.</div>
                </div>
                <div className="mb-3">
                  <label className="form-label">Role</label>
                  <select
                    className={`form-select ${formErrors.role ? 'is-invalid' : ''}`}
                    value={form.role}
                    onChange={(e) => setForm({ ...form, role: e.target.value as any })}
                  >
                    <option value="host">Host</option>
                    <option value="manager">Manager</option>
                    <option value="owner">Owner</option>
                  </select>
                  {formErrors.role && <div className="invalid-feedback d-block">{formErrors.role[0]}</div>}
                </div>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-dark-ghost btn-sm" onClick={closeModals}>Cancel</button>
                <LoadingButton type="submit" isLoading={isSubmitting} className="btn btn-gold btn-sm">
                  Update Account
                </LoadingButton>
              </div>
            </form>
          </div>
        </div>
      </div>

      {/* ============================================================ */}
      {/* DELETE MODAL */}
      {/* ============================================================ */}
      <div
        className={`modal fade ${isDeleteModalOpen ? 'show d-block' : ''}`}
        style={{ display: isDeleteModalOpen ? 'block' : 'none', background: isDeleteModalOpen ? 'rgba(0,0,0,0.5)' : 'none' }}
        onClick={(e) => { if (e.target === e.currentTarget) closeModals(); }}
      >
        <div className="modal-dialog modal-dialog-centered modal-sm">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title fw-bold text-rust">Delete Staff Account</h5>
              <button type="button" className="btn-close" onClick={closeModals}></button>
            </div>
            <div className="modal-body">
              <p className="mb-0">
                Are you sure you want to delete <strong>{selectedUser?.name}</strong>'s account?
                <br />
                <span className="text-muted-soft small">This action cannot be undone.</span>
              </p>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-dark-ghost btn-sm" onClick={closeModals}>Cancel</button>
              <button type="button" className="btn btn-outline-danger btn-sm" onClick={handleDelete}>
                <i className="bi bi-trash me-1"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      {toast && <Toast message={toast.message} type={toast.type} />}
    </DashboardLayout>
  );
}