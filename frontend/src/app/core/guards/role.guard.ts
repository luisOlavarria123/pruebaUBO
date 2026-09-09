import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { UserRole } from '../models/user.model';

/**
 * Restringe una ruta a un rol especifico. Uso: data: { role: 'revisor' }.
 * Asume que authGuard ya corrio antes (se combinan en la definicion de ruta).
 */
export const roleGuard: CanActivateFn = (route) => {
  const auth = inject(AuthService);
  const router = inject(Router);
  const requiredRole = route.data['role'] as UserRole | undefined;

  if (!requiredRole || auth.currentUser()?.role === requiredRole) {
    return true;
  }

  return router.createUrlTree(['/solicitudes']);
};
