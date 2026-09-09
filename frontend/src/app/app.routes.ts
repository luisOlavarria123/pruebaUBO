import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { roleGuard } from './core/guards/role.guard';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'solicitudes' },
  {
    path: 'login',
    loadComponent: () => import('./features/login/login').then((m) => m.Login),
  },
  {
    path: 'solicitudes',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/solicitudes-list/solicitudes-list').then((m) => m.SolicitudesList),
  },
  {
    path: 'solicitudes/nueva',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/solicitud-form/solicitud-form').then((m) => m.SolicitudForm),
  },
  {
    path: 'solicitudes/:id/editar',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/solicitud-form/solicitud-form').then((m) => m.SolicitudForm),
  },
  {
    path: 'solicitudes/:id',
    canActivate: [authGuard],
    loadComponent: () =>
      import('./features/solicitud-detail/solicitud-detail').then((m) => m.SolicitudDetail),
  },
  {
    path: 'categorias',
    canActivate: [authGuard, roleGuard],
    data: { role: 'revisor' },
    loadComponent: () => import('./features/categorias/categorias').then((m) => m.Categorias),
  },
  { path: '**', redirectTo: 'solicitudes' },
];
