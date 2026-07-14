import { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface Restaurant {
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

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'owner' | 'manager' | 'host';
  restaurant_id: number;
  avatar_initials?: string;
}

export interface PageProps extends InertiaPageProps {
  user: User;
  restaurant: Restaurant;
  errors?: Record<string, string[]>;
  flash?: {
    message?: string;
    error?: string;
  };
}