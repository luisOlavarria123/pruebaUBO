import { HttpClient } from '@angular/common/http';
import { Injectable, computed, signal } from '@angular/core';
import { Observable, map, tap } from 'rxjs';
import { API_URL } from '../config/api-url';
import { User } from '../models/user.model';

interface LoginResponse {
  access_token: string;
  token_type: string;
  expires_in: number;
  user: User;
}

const STORAGE_KEY = 'solicitudes.auth';

interface StoredAuth {
  token: string;
  user: User;
  expiresAt: number;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly state = signal<StoredAuth | null>(this.readFromStorage());

  readonly currentUser = computed(() => this.state()?.user ?? null);
  readonly isAuthenticated = computed(() => {
    const stored = this.state();
    return !!stored && stored.expiresAt > Date.now();
  });

  constructor(private readonly http: HttpClient) {}

  get token(): string | null {
    const stored = this.state();
    return stored && stored.expiresAt > Date.now() ? stored.token : null;
  }

  login(email: string, password: string): Observable<User> {
    return this.http.post<LoginResponse>(`${API_URL}/login`, { email, password }).pipe(
      tap({
        next: (response) => {
          const stored: StoredAuth = {
            token: response.access_token,
            user: response.user,
            expiresAt: Date.now() + response.expires_in * 1000,
          };
          localStorage.setItem(STORAGE_KEY, JSON.stringify(stored));
          this.state.set(stored);
        },
        error: () => this.clear(),
      }),
      map((response) => response.user),
    );
  }

  logout(): void {
    if (!this.token) {
      this.clear();
      return;
    }

    // Invalida el token en el backend mientras todavia es valido; la sesion
    // local se limpia pase lo que pase (best effort del lado del servidor).
    this.http.post(`${API_URL}/logout`, {}).subscribe({
      next: () => this.clear(),
      error: () => this.clear(),
    });
  }

  /** Llamado por el interceptor cuando el backend responde 401. */
  clear(): void {
    localStorage.removeItem(STORAGE_KEY);
    this.state.set(null);
  }

  private readFromStorage(): StoredAuth | null {
    const raw = localStorage.getItem(STORAGE_KEY);

    if (!raw) {
      return null;
    }

    try {
      const parsed = JSON.parse(raw) as StoredAuth;

      return parsed.expiresAt > Date.now() ? parsed : null;
    } catch {
      return null;
    }
  }
}
