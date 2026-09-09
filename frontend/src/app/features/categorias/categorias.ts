import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { CategoriaService } from '../../core/services/categoria.service';
import { ApiError } from '../../core/models/api.model';
import { Categoria } from '../../core/models/categoria.model';

@Component({
  selector: 'app-categorias',
  imports: [ReactiveFormsModule],
  templateUrl: './categorias.html',
  styleUrl: './categorias.css',
})
export class Categorias implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly categoriaService = inject(CategoriaService);

  readonly categorias = signal<Categoria[]>([]);
  readonly loading = signal(true);
  readonly errorMessage = signal<string | null>(null);
  readonly rowError = signal<string | null>(null);
  readonly saving = signal(false);

  readonly createForm = this.fb.nonNullable.group({
    nombre: ['', Validators.required],
  });

  ngOnInit(): void {
    this.load();
  }

  create(): void {
    if (this.createForm.invalid || this.saving()) {
      this.createForm.markAllAsTouched();
      return;
    }

    this.saving.set(true);
    this.errorMessage.set(null);

    this.categoriaService.create({ nombre: this.createForm.getRawValue().nombre, activa: true }).subscribe({
      next: () => {
        this.saving.set(false);
        this.createForm.reset();
        this.load();
      },
      error: (error: unknown) => {
        this.saving.set(false);
        this.errorMessage.set(this.extractMessage(error));
      },
    });
  }

  toggleActiva(categoria: Categoria): void {
    this.rowError.set(null);

    this.categoriaService.update(categoria.id, { nombre: categoria.nombre, activa: !categoria.activa }).subscribe({
      next: () => this.load(),
      error: (error: unknown) => this.rowError.set(this.extractMessage(error)),
    });
  }

  remove(categoria: Categoria): void {
    if (!confirm(`¿Eliminar la categoria "${categoria.nombre}"?`)) {
      return;
    }

    this.rowError.set(null);

    this.categoriaService.delete(categoria.id).subscribe({
      next: () => this.load(),
      error: (error: unknown) => this.rowError.set(this.extractMessage(error)),
    });
  }

  private load(): void {
    this.loading.set(true);

    this.categoriaService.list().subscribe({
      next: (categorias) => {
        this.categorias.set(categorias);
        this.loading.set(false);
      },
      error: (error: unknown) => {
        this.loading.set(false);
        this.errorMessage.set(this.extractMessage(error));
      },
    });
  }

  private extractMessage(error: unknown): string {
    if (error instanceof HttpErrorResponse) {
      const body = error.error as ApiError | undefined;

      if (body?.message) {
        return body.message;
      }
    }

    return 'Ocurrio un error inesperado. Intenta nuevamente.';
  }
}
