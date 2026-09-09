export interface ApiResource<T> {
  data: T;
}

export interface ApiCollection<T> {
  data: T[];
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ApiError {
  message: string;
  errors: Record<string, string[]> | null;
}
