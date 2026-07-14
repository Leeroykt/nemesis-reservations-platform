import { describe, it, expect, vi, beforeEach } from 'vitest';

// Mock axios BEFORE importing ApiClient
vi.mock('axios', () => {
  const mockAxiosInstance = {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
    patch: vi.fn(),
    interceptors: {
      request: { use: vi.fn(), eject: vi.fn() },
      response: { use: vi.fn(), eject: vi.fn() },
    },
  };

  return {
    default: {
      create: vi.fn(() => mockAxiosInstance),
      get: vi.fn(),
      post: vi.fn(),
      put: vi.fn(),
      delete: vi.fn(),
      patch: vi.fn(),
      interceptors: {
        request: { use: vi.fn(), eject: vi.fn() },
        response: { use: vi.fn(), eject: vi.fn() },
      },
    },
  };
});

// NOW import ApiClient after mocking
import axios from 'axios';
import { ApiClient } from '@/lib/api';

describe('API Client', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should fetch CSRF cookie before login', async () => {
    const mockInstance = {
      get: vi.fn().mockResolvedValue({ data: {} }),
      post: vi.fn(),
      put: vi.fn(),
      delete: vi.fn(),
      patch: vi.fn(),
      interceptors: {
        request: { use: vi.fn(), eject: vi.fn() },
        response: { use: vi.fn(), eject: vi.fn() },
      },
    };

    (axios.create as any).mockReturnValue(mockInstance);

    const api = new ApiClient();
    await api.fetchCsrf();
    expect(mockInstance.get).toHaveBeenCalledWith('/sanctum/csrf-cookie', { withCredentials: true });
  });

  it('should login and return user data', async () => {
    const mockInstance = {
      get: vi.fn().mockResolvedValue({ data: {} }),
      post: vi.fn().mockResolvedValue({
        data: {
          data: {
            user: { id: 1, name: 'Admin', role: 'owner' },
          },
        },
      }),
      put: vi.fn(),
      delete: vi.fn(),
      patch: vi.fn(),
      interceptors: {
        request: { use: vi.fn(), eject: vi.fn() },
        response: { use: vi.fn(), eject: vi.fn() },
      },
    };

    (axios.create as any).mockReturnValue(mockInstance);

    const api = new ApiClient();
    const result = await api.login('test@example.com', 'password');
    expect(result.user).toEqual({ id: 1, name: 'Admin', role: 'owner' });
    expect(mockInstance.post).toHaveBeenCalledWith('/login', { email: 'test@example.com', password: 'password' });
  });

  it('should handle logout', async () => {
    const mockInstance = {
      get: vi.fn(),
      post: vi.fn().mockResolvedValue({ data: {} }),
      put: vi.fn(),
      delete: vi.fn(),
      patch: vi.fn(),
      interceptors: {
        request: { use: vi.fn(), eject: vi.fn() },
        response: { use: vi.fn(), eject: vi.fn() },
      },
    };

    (axios.create as any).mockReturnValue(mockInstance);

    const api = new ApiClient();
    await api.logout();
    expect(mockInstance.post).toHaveBeenCalledWith('/logout');
  });
});