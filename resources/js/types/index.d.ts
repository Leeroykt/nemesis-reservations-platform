import { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface Restaurant {
  id: number;
  name: string;
  timezone: string;
  // add other fields as needed
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'owner' | 'manager' | 'host';
}

export interface PageProps extends InertiaPageProps {
  user: User;
  restaurant: Restaurant;
  // ... any other shared props
}