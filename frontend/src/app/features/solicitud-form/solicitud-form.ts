import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { CategoriaService } from '../../core/services/categoria.service';
import { SolicitudService } from '../../core/services/solicitud.service';
import { Categoria } from '../../core/models/categoria.model';
import { ApiError } from '../../core/models/api.model';
import { SolicitudTipo } from '../../core/models/solicitud.model';

const URGENCIAS = ['baja', 'media', 'alta'] as const;

@Component({
  selector: 'app-solicitud-form',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './solicitud-form.html',
  styleUrl: './solicitud-form.css',
})
export class SolicitudForm implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly categoriaService = inject(CategoriaService);
  private readonly solicitudService = inject(SolicitudService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  readonly urgencias = URGENCIAS;
  readonly categorias = signal<Categoria[]>([]);
  readonly loading = signal(false);
  readonly loadingInicial = signal(false);
  readonly errorMessage = signal<string | null>(null);

  private solicitudId: number | null = null;

  readonly form = this.fb.nonNullable.group({
    categoria_id: ['', Validators.required],
    tipo: ['' as SolicitudTipo | '', Validators.required],
    descripcion: ['', [Validators.required, Validators.maxLength(2000)]],
    urgencia: [''],
  });

  get isEdit(): boolean {
    return this.solicitudId !== null;
  }

  ngOnInit(): void {
    this.categoriaService.list().subscribe({
      next: (categorias) => this.categorias.set(categorias),
      error: () => undefined,
    });

    // La misma pantalla sirve para crear y editar (mismo formulario, misma
    // validacion cruzada); en modo edicion solo cambia el metodo HTTP.
    this.form.controls.tipo.valueChanges.subscribe((tipo) => this.syncUrgenciaValidator(tipo));

    const idParam = this.route.snapshot.paramMap.get('id');

    if (idParam) {
      this.solicitudId = Number(idParam);
      this.loadingInicial.set(true);

      this.solicitudService.get(this.solicitudId).subscribe({
        next: (solicitud) => {
          this.form.patchValue({
            categoria_id: String(solicitud.categoria?.id ?? ''),
            tipo: solicitud.tipo,
            descripcion: solicitud.descripcion,
            urgencia: (solicitud.campos_adicionales?.['urgencia'] as string) ?? '',
          });
          this.syncUrgenciaValidator(solicitud.tipo);
          this.loadingInicial.set(false);
        },
        error: () => {
          this.loadingInicial.set(false);
          this.errorMessage.set('No se pudo cargar la solicitud a editar.');
        },
      });
    }
  }

  submit(): void {
    if (this.form.invalid || this.loading()) {
      this.form.markAllAsTouched();
      return;
    }

    this.loading.set(true);
    this.errorMessage.set(null);

    const raw = this.form.getRawValue();
    const payload = {
      categoria_id: Number(raw.categoria_id),
      tipo: raw.tipo as SolicitudTipo,
      descripcion: raw.descripcion,
      campos_adicionales: raw.tipo === 'incidente' ? { urgencia: raw.urgencia } : {},
    };

    const request = this.isEdit
      ? this.solicitudService.update(this.solicitudId!, payload)
      : this.solicitudService.create(payload);

    request.subscribe({
      next: (solicitud) => this.router.navigate(['/solicitudes', solicitud.id]),
      error: (error: unknown) => {
        this.loading.set(false);
        this.applyServerErrors(error);
      },
    });
  }

  private syncUrgenciaValidator(tipo: string | null): void {
    const urgencia = this.form.controls.urgencia;

    if (tipo === 'incidente') {
      urgencia.setValidators([Validators.required]);
    } else {
      urgencia.setValidators([]);
      urgencia.setValue('');
    }

    urgencia.updateValueAndValidity();
  }

  private applyServerErrors(error: unknown): void {
    if (!(error instanceof HttpErrorResponse)) {
      this.errorMessage.set('Ocurrio un error inesperado. Intenta nuevamente.');
      return;
    }

    const body = error.error as ApiError | undefined;
    this.errorMessage.set(body?.message ?? 'No se pudo guardar la solicitud.');

    const fieldMap: Record<string, keyof typeof this.form.controls> = {
      categoria_id: 'categoria_id',
      tipo: 'tipo',
      descripcion: 'descripcion',
      'campos_adicionales.urgencia': 'urgencia',
    };

    for (const [backendKey, controlName] of Object.entries(fieldMap)) {
      if (body?.errors?.[backendKey]) {
        this.form.controls[controlName].setErrors({ server: body.errors[backendKey][0] });
      }
    }
  }
}
