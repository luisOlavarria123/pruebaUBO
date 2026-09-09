export type UserRole = 'solicitante' | 'revisor';

export interface User {
  id: number;
  name: string;
  email: string;
  role: UserRole;
}
