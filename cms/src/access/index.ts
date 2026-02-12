import type { Access, FieldAccess } from 'payload';

type UserWithRole = {
  id: string;
  role?: 'admin' | 'editor';
  [key: string]: unknown;
};

/**
 * Any authenticated user.
 */
export const isAuthenticated: Access = ({ req: { user } }) => Boolean(user);

/**
 * Only users with role 'admin'.
 */
export const isAdmin: Access = ({ req: { user } }) => {
  const u = user as UserWithRole | null;
  return u?.role === 'admin' || false;
};

/**
 * Admin-only at field level (e.g. read-only fields for editors).
 */
export const isAdminFieldLevel: FieldAccess = ({ req: { user } }) => {
  const u = user as UserWithRole | null;
  return u?.role === 'admin' || false;
};

/**
 * Anyone can read, only authenticated users can write.
 */
export const publicReadOnly: Access = () => true;
