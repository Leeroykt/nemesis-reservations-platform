import axios, { AxiosError, AxiosInstance, AxiosResponse } from 'axios';

export interface ApiResponse<T = any> {
  data: T;
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

  public async fetchCsrf(): Promise<void> {
    // ✅ FIXED: Use this.authClient instead of axios directly
    await this.authClient.get('/sanctum/csrf-cookie', { withCredentials: true });
  }

  public async login(email: string, password: string): Promise<{ user: any }> {
    await this.fetchCsrf();
    const response = await this.authClient.post('/login', { email, password });
    return response.data.data;
  }

  public logout(): Promise<void> {
    return this.authClient.post('/logout').then(() => {});
  }

  public me(): Promise<any> {
    return this.authClient.get('/me').then((res) => res.data.data);
  }

  public get<T = any>(url: string, params?: Record<string, any>): Promise<ApiResponse<T>> {
    return this.apiClient.get<ApiResponse<T>>(url, { params }).then((res) => res.data);
  }

  public post<T = any>(url: string, data?: any): Promise<ApiResponse<T>> {
    return this.apiClient.post<ApiResponse<T>>(url, data).then((res) => res.data);
  }

  public patch<T = any>(url: string, data?: any): Promise<ApiResponse<T>> {
    return this.apiClient.patch<ApiResponse<T>>(url, data).then((res) => res.data);
  }

  public delete<T = any>(url: string): Promise<ApiResponse<T>> {
    return this.apiClient.delete<ApiResponse<T>>(url).then((res) => res.data);
  }
}

// ✅ Export both class and singleton instance
export const api = new ApiClient();
