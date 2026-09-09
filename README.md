# Sistema de Gestión de Solicitudes

Prueba técnica fullstack: API REST desacoplada en Laravel (JWT) + SPA en Angular + Docker Compose.

Dominio elegido: **solicitudes de soporte** (incidentes, consultas, mejoras), con dos roles —
`solicitante` (crea y ve las propias) y `revisor` (ve todas, filtra, elimina, cambia estado).

## Levantar el proyecto

Requisito único: Docker Desktop corriendo.

```bash
docker-compose up
```

Levanta tres servicios — `db` (MySQL 8), `backend` (Laravel API en `:8000`) y `frontend` (Angular
dev server en `:4200`) — con un solo comando. El contenedor `backend` espera a que la base de datos
acepte conexiones, corre las migraciones y siembra datos de prueba automáticamente en cada arranque.

Abrir **http://localhost:4200**.

### Credenciales de prueba

| Rol | Email | Password |
|---|---|---|
| Solicitante | `solicitante@ubo.test` | `password` |
| Revisor | `revisor@ubo.test` | `password` |

### Variables de entorno

Las variables del backend están declaradas directamente en `docker-compose.yml` (incluyendo
`APP_KEY` y `JWT_SECRET` ya generados) para que `docker-compose up` funcione sin pasos manuales
adicionales. `backend/.env.example` documenta el mismo set de variables para quien quiera correr el
backend fuera de Docker. **Nota de seguridad**: esos secretos están committeados a propósito porque
son de un entorno de prueba local, descartable — en un repo real irían en un `.env` no versionado o
en un vault, nunca en el compose file.

## Decisiones de arquitectura y por qué

### Backend: capas explícitas, controladores delgados

`Ruta → Form Request → Controller → Service → Model → Resource`. Cada capa tiene una sola
responsabilidad: los Form Requests validan y autorizan en el borde, los controladores solo
orquestan (2-4 líneas por método), la lógica de negocio real vive en `SolicitudService`, y las API
Resources garantizan que nunca se expone un modelo Eloquent crudo. El detalle completo de esta
decisión y sus trade-offs (por qué la máquina de estados vive en un enum y no en el servicio, por
qué reutilizo `ValidationException` en vez de excepciones custom, por qué no hay
repository/DTO/interfaces de una sola implementación) está documentado como comentarios y en la
estructura misma del código — ver `app/Services/SolicitudService.php`, `app/Enums/SolicitudEstado.php`
y `app/Exceptions/ApiExceptionRenderer.php`.

### Frontend: `core/` vs `features/`, señales en vez de NgRx

Separación entre infraestructura transversal (`core/` — auth, HTTP, guards) y pantallas ruteadas
(`features/`, cada una lazy-loaded). El estado de sesión vive en una única señal dentro de
`AuthService` (`signal<StoredAuth | null>` + `computed()` derivados); los datos de dominio
(solicitudes, categorías) no se comparten entre pantallas, así que cada feature mantiene sus propias
señales locales de `loading`/`error`/`data`. No hay store global porque no hay nada que
genuinamente necesite compartirse entre dos pantallas a la vez — traer NgRx acá sería la misma
sobreingeniería que evité en el backend con interfaces de una sola implementación.

### Autenticación: JWT stateless, sin sesiones de servidor

`php-open-source-saver/jwt-auth` (fork mantenido de tymon/jwt-auth). El guard `api` es la única
guard registrada — no hay rutas web ni sesiones, todo el backend es una API pura. El token vive 60
minutos (`JWT_TTL`), se guarda en `localStorage` del lado del cliente, y un interceptor HTTP
(`authInterceptor`) lo adjunta a cada request y limpia la sesión automáticamente ante un 401 — sin
que cada componente tenga que manejar la expiración por su cuenta.

### Docker: simple a propósito

`backend` corre `php artisan serve` en un solo contenedor, no Nginx + PHP-FPM separados.
`frontend` corre `ng serve`,
no un build de producción servido por Nginx. Ambas son decisiones de tiempo, no de desconocimiento
— quedan documentadas explícitamente en "Qué le falta para producción" más abajo, tal como pide el
enunciado en vez de omitirlas en silencio.

## Qué dejaría distinto si tuviera más tiempo

- **Extraer el manejo de errores del frontend a un helper compartido.** La función que traduce un
  `HttpErrorResponse` del backend a un mensaje legible (`extractMessage`) está casi idéntica y
  duplicada en `login.ts`, `solicitud-form.ts`, `solicitud-detail.ts`, `categorias.ts` y
  `solicitudes-list.ts`. No la extraje antes porque quería ver los cinco casos reales escritos
  antes de generalizar (evitar abstraer sobre un solo ejemplo), pero con el código ya estable esto
  es una refactorización directa a `core/utils/`.
- **Endpoint de refresh de token.** Hoy, al expirar el JWT (1h), el usuario tiene que volver a
  loguearse — el paquete de JWT soporta refresh nativamente (`auth('api')->refresh()`) pero no
  llegué a exponerlo ni a implementar la renovación silenciosa en el frontend.
- **Tests de frontend.** El backend tiene 18 tests de feature cubriendo la máquina de estados, las
  reglas de negocio y el scoping por rol; el frontend no tiene tests automatizados — lo validé
  manualmente en el navegador de punta a punta (login, CRUD completo, transiciones de estado,
  errores 422/403/409), pero no hay `*.spec.ts` para eso.


## Qué le faltaría para producción

- **Servir el backend con Nginx + PHP-FPM (o similar)**, no `artisan serve` — ese comando está
  documentado por Laravel mismo como no apto para producción.
- **Build de producción del frontend** (`ng build` + servir el `dist/` estático con Nginx), no el
  dev server con hot-reload.
- **Rotar los secretos** (`APP_KEY`, `JWT_SECRET`, credenciales de MySQL) fuera del
  `docker-compose.yml` — hoy están committeados a propósito para que el proyecto arranque con un
  solo comando sin pasos manuales, pero eso es aceptable únicamente porque es un entorno de prueba.
- **HTTPS** en ambos extremos; hoy todo corre en HTTP plano sobre `localhost`.
- **Rate limiting** en el endpoint de login (y en general) — no hay throttling contra fuerza bruta.
- **Refresh token** en vez de forzar re-login cada hora.
- **Logging/monitoreo** centralizado (hoy los logs quedan solo dentro del contenedor).
- **CI/CD**: hay un esqueleto de GitHub Actions (`.github/workflows/tests.yml`) que corre los tests
  del backend, pero no build/push de imágenes ni deploy.
- **Backups de la base de datos** — el volumen de MySQL persiste localmente, pero no hay estrategia
  de backup/restore.
- **Tests de frontend** (ver arriba).

## Tiempo real invertido
La sesión de trabajo activa cubrió aproximadamente 6 horas.