import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { CategoriaService } from '../../core/services/categoria.service';
import { SolicitudService } from '../../core/services/solicitud.service';
import { Categoria } from '../../core/models/categoria.model';
import { PaginatedResponse } from '../../core/models/api.model';
import { Solicitud, SolicitudEstado } from '../../core/models/solicitud.model';

const ESTADOS: SolicitudEstado[] = ['pendiente', 'en_revision', 'aprobada', 'rechazada'];

@Component({
  selector: 'app-solicitudes-list',
  imports: [FormsModule, RouterLink],
  templateUrl: './solicitudes-list.html',
  styleUrl: './solicitudes-list.css',
})
export class SolicitudesList implements OnInit {
  readonly estados = ESTADOS;
  readonly categorias = signal<Categoria[]>([]);

  readonly solicitudes = signal<Solicitud[]>([]);
  readonly meta = signal<PaginatedResponse<Solicitud>['meta'] | null>(null);
  readonly loading = signal(true);
  readonly errorMessage = signal<string | null>(null);

  estadoFilter = '';
  categoriaFilter = '';
  sortBy: 'created_at' | 'estado' | 'tipo' = 'created_at';
  sortDir: 'asc' | 'desc' = 'desc';
  page = 1;

  constructor(
    private readonly solicitudService: SolicitudService,
    private readonly categoriaService: CategoriaService,
    readonly auth: AuthService,
  ) {}

  ngOnInit(): void {
    this.categoriaService.list().subscribe({
      next: (categorias) => this.categorias.set(categorias),
      error: () => undefined,
    });

    this.fetch();
  }

  onFiltersChange(): void {
    this.page = 1;
    this.fetch();
  }

  goToPage(page: number): void {
    if (page < 1 || (this.meta() && page > this.meta()!.last_page)) {
      return;
    }

    this.page = page;
    this.fetch();
  }

  private fetch(): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.solicitudService
      .list({
        estado: (this.estadoFilter || undefined) as SolicitudEstado | undefined,
        categoria_id: this.categoriaFilter ? Number(this.categoriaFilter) : undefined,
        sort_by: this.sortBy,
        sort_dir: this.sortDir,
        page: this.page,
      })
      .subscribe({
        next: (response) => {
          this.solicitudes.set(response.data);
          this.meta.set(response.meta);
          this.loading.set(false);
        },
        error: (error: unknown) => {
          this.loading.set(false);
          this.errorMessage.set(
            error instanceof HttpErrorResponse && error.status === 0
              ? 'No se pudo conectar con el servidor.'
              : 'No se pudo cargar el listado de solicitudes.',
          );
        },
      });
  }
}
