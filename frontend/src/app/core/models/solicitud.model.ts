import { Categoria } from './categoria.model';
import { User } from './user.model';

export type SolicitudTipo = 'incidente' | 'consulta' | 'mejora';

export type SolicitudEstado = 'pendiente' | 'en_revision' | 'aprobada' | 'rechazada';

export interface Solicitud {
  id: number;
  tipo: SolicitudTipo;
  descripcion: string;
  campos_adicionales: Record<string, unknown>;
  estado: SolicitudEstado;
  estados_siguientes: SolicitudEstado[];
  solicitante?: User;
  categoria?: Categoria;
  created_at: string;
  updated_at: string;
}

export interface SolicitudPayload {
  categoria_id: number;
  tipo: SolicitudTipo;
  descripcion: string;
  campos_adicionales?: Record<string, unknown>;
}

export interface SolicitudFilters {
  estado?: SolicitudEstado | '';
  categoria_id?: number | '';
  sort_by?: 'created_at' | 'estado' | 'tipo';
  sort_dir?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}
