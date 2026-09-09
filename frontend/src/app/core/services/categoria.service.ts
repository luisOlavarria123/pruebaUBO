import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';
import { API_URL } from '../config/api-url';
import { ApiResource, ApiCollection } from '../models/api.model';
import { Categoria } from '../models/categoria.model';

export interface CategoriaPayload {
  nombre: string;
  activa: boolean;
}

@Injectable({ providedIn: 'root' })
export class CategoriaService {
  constructor(private readonly http: HttpClient) {}

  list(): Observable<Categoria[]> {
    return this.http
      .get<ApiCollection<Categoria>>(`${API_URL}/categorias`)
      .pipe(map((response) => response.data));
  }

  create(payload: CategoriaPayload): Observable<Categoria> {
    return this.http
      .post<ApiResource<Categoria>>(`${API_URL}/categorias`, payload)
      .pipe(map((response) => response.data));
  }

  update(id: number, payload: CategoriaPayload): Observable<Categoria> {
    return this.http
      .put<ApiResource<Categoria>>(`${API_URL}/categorias/${id}`, payload)
      .pipe(map((response) => response.data));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${API_URL}/categorias/${id}`);
  }
}
