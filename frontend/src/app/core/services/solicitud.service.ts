import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';
import { API_URL } from '../config/api-url';
import { ApiResource, PaginatedResponse } from '../models/api.model';
import { Solicitud, SolicitudEstado, SolicitudFilters, SolicitudPayload } from '../models/solicitud.model';

@Injectable({ providedIn: 'root' })
export class SolicitudService {
  constructor(private readonly http: HttpClient) {}

  list(filters: SolicitudFilters): Observable<PaginatedResponse<Solicitud>> {
    let params = new HttpParams();

    for (const [key, value] of Object.entries(filters)) {
      if (value !== '' && value !== undefined && value !== null) {
        params = params.set(key, String(value));
      }
    }

    return this.http.get<PaginatedResponse<Solicitud>>(`${API_URL}/solicitudes`, { params });
  }

  get(id: number): Observable<Solicitud> {
    return this.http
      .get<ApiResource<Solicitud>>(`${API_URL}/solicitudes/${id}`)
      .pipe(map((response) => response.data));
  }

  create(payload: SolicitudPayload): Observable<Solicitud> {
    return this.http
      .post<ApiResource<Solicitud>>(`${API_URL}/solicitudes`, payload)
      .pipe(map((response) => response.data));
  }

  update(id: number, payload: SolicitudPayload): Observable<Solicitud> {
    return this.http
      .put<ApiResource<Solicitud>>(`${API_URL}/solicitudes/${id}`, payload)
      .pipe(map((response) => response.data));
  }

  updateEstado(id: number, estado: SolicitudEstado): Observable<Solicitud> {
    return this.http
      .patch<ApiResource<Solicitud>>(`${API_URL}/solicitudes/${id}/estado`, { estado })
      .pipe(map((response) => response.data));
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`${API_URL}/solicitudes/${id}`);
  }
}
