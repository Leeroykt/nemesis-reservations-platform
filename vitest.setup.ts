import '@testing-library/jest-dom/vitest';
import { vi } from 'vitest';
import React from 'react';

// NOTE: axios mock is NOW in api.test.tsx only (not here)

// Mock Inertia
vi.mock('@inertiajs/react', () => ({
  usePage: vi.fn(() => ({
    props: { user: { name: 'Test User', role: 'owner' } },
  })),
  Link: ({ children, href }: { children: React.ReactNode; href: string }) => {
    return React.createElement('a', { href }, children);
  },
  router: {
    visit: vi.fn(),
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
  useForm: vi.fn(() => ({
    data: {},
    setData: vi.fn(),
    post: vi.fn(),
    processing: false,
    errors: {},
  })),
}));