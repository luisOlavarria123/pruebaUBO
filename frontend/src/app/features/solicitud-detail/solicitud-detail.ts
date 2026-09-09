import { DatePipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, computed, signal } from '@angular/core';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { SolicitudService } from '../../core/services/solicitud.service';
import { ApiError } from '../../core/models/api.model';
import { Solicitud, SolicitudEstado } from '../../core/models/solicitud.model';

@Component({
  selector: 'app-solicitud-detail',
  imports: [RouterLink, DatePipe],
  templateUrl: './solicitud-detail.html',
  styleUrl: './solicitud-detail.css',
})
export class SolicitudDetail implements OnInit {
  readonly solicitud = signal<Solicitud | null>(null);
  readonly loading = signal(true);
  readonly errorMessage = signal<string | null>(null);
  readonly actionLoading = signal(false);
  readonly actionError = signal<string | null>(null);

  readonly canEdit = computed(() => {
    const s = this.solicitud();
    const user = this.auth.currentUser();
    return !!s && !!user && user.role === 'solicitante' && s.solicitante?.id === user.id && s.estado === 'pendiente';
  });

  readonly canDelete = computed(() => this.auth.currentUser()?.role === 'revisor');
  readonly canChangeEstado = computed(
    () => this.auth.currentUser()?.role === 'revisor' && (this.solicitud()?.estados_siguientes.length ?? 0) > 0,
  );

  private id!: number;

  constructor(
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly solicitudService: SolicitudService,
    readonly auth: AuthService,
  ) {}

  ngOnInit(): void {
    this.id = Number(this.route.snapshot.paramMap.get('id'));
    this.load();
  }

  changeEstado(estado: SolicitudEstado): void {
    this.actionLoading.set(true);
    this.actionError.set(null);

    this.solicitudService.updateEstado(this.id, estado).subscribe({
      next: (solicitud) => {
        this.solicitud.set(solicitud);
        this.actionLoading.set(false);
      },
      error: (error: unknown) => {
        this.actionLoading.set(false);
        this.actionError.set(this.extractMessage(error));
      },
    });
  }

  remove(): void {
    if (!confirm('¿Eliminar esta solicitud? Esta accion no se puede deshacer.')) {
      return;
    }

    this.actionLoading.set(true);
    this.actionError.set(null);

    this.solicitudService.delete(this.id).subscribe({
      next: () => this.router.navigateByUrl('/solicitudes'),
      error: (error: unknown) => {
        this.actionLoading.set(false);
        this.actionError.set(this.extractMessage(error));
      },
    });
  }

  private load(): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.solicitudService.get(this.id).subscribe({
      next: (solicitud) => {
        this.solicitud.set(solicitud);
        this.loading.set(false);
      },
      error: (error: unknown) => {
        this.loading.set(false);
        this.errorMessage.set(this.extractMessage(error, true));
      },
    });
  }

  private extractMessage(error: unknown, isLoad = false): string {
    if (error instanceof HttpErrorResponse) {
      const body = error.error as ApiError | undefined;

      if (error.status === 403) {
        return 'No tienes permiso para ' + (isLoad ? 'ver esta solicitud.' : 'realizar esta accion.');
      }

      if (error.status === 404) {
        return 'La solicitud no existe.';
      }

      if (body?.message) {
        return body.message;
      }
    }

    return 'Ocurrio un error inesperado. Intenta nuevamente.';
  }
}
