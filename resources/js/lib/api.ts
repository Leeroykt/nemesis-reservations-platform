import axios, { AxiosError, AxiosInstance } from 'axios';

// ============================================================
// TYPES
// ============================================================

export interface ApiResponse<T = any> {
  data: T;
  message?: string;
  meta?: {
    total: number;
    page: number;
    perPage: number;
    hasMore: boolean;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'host' | 'manager' | 'owner';
  avatar_initials?: string;
  restaurant_id: number;
  created_at: string;
  updated_at: string;
}

export interface LoginResponse {
  user: User;
}

// ============================================================
// API CLIENT
// ============================================================

export class ApiClient {
  private apiClient: AxiosInstance;
  private authClient: AxiosInstance;

  constructor() {
    const baseConfig = {
      withCredentials: true,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    };

    this.apiClient = axios.create({
      baseURL: '/api/v1',
      ...baseConfig,
    });

    this.authClient = axios.create({
      baseURL: '',
      ...baseConfig,
    });

    const errorInterceptor = (error: AxiosError) => {
      if (error.response?.status === 401) {
        if (typeof window !== 'undefined') {
          window.location.href = '/login';
        }
      }
      const apiError: ApiError = {
        message: 'An unexpected error occurred.',
      };
      if (error.response?.data) {
        const data = error.response.data as any;
        if (data.message) apiError.message = data.message;
        if (data.errors) apiError.errors = data.errors;
      }
      return Promise.reject(apiError);
    };

    this.apiClient.interceptors.response.use((res) => res, errorInterceptor);
    this.authClient.interceptors.response.use((res) => res, errorInterceptor);
  }

  // ============================================================
  // AUTH METHODS
  // ============================================================

  public async fetchCsrf(): Promise<void> {
    await this.authClient.get('/sanctum/csrf-cookie', { withCredentials: true });
  }

  public async login(email: string, password: string): Promise<LoginResponse> {
    await this.fetchCsrf();
    const response = await this.authClient.post('/login', { email, password });
    return response.data.data;
  }

  public async logout(): Promise<void> {
    await this.authClient.post('/logout');
  }

  public async me(): Promise<User> {
    const response = await this.authClient.get('/me');
    return response.data.data;
  }

  // ============================================================
  // API METHODS
  // ============================================================

  public async get<T = any>(url: string, params?: Record<string, any>): Promise<ApiResponse<T>> {
    const response = await this.apiClient.get<ApiResponse<T>>(url, { params });
    return response.data;
  }

  public async post<T = any>(url: string, data?: any): Promise<ApiResponse<T>> {
    const response = await this.apiClient.post<ApiResponse<T>>(url, data);
    return response.data;
  }

  public async patch<T = any>(url: string, data?: any): Promise<ApiResponse<T>> {
    const response = await this.apiClient.patch<ApiResponse<T>>(url, data);
    return response.data;
  }

  public async delete<T = any>(url: string): Promise<ApiResponse<T>> {
    const response = await this.apiClient.delete<ApiResponse<T>>(url);
    return response.data;
  }
}

export const api = new ApiClient();