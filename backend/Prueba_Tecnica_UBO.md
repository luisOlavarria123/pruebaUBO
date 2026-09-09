**Prueba Técnica — Desarrollador/a Fullstack**

_Sistema de Gestión de Solicitudes (SPA + API desacoplada)_

# 1\. Contexto

Nuestro equipo está migrando de un modelo monolítico legado hacia una arquitectura desacoplada: SPA en Angular, backend Laravel expuesto vía API REST, autenticación JWT, y despliegue mediante Docker como base para un futuro pipeline de CI/CD.

Duración estimada: medio día (5–6 horas).

# 2\. Caso: Sistema de Solicitudes

Construye un sistema pequeño para gestionar "solicitudes" (puede ser de cualquier tipo — ej. solicitudes de soporte, de compra, de admisión a un curso; elige el dominio que prefieras) con dos entidades relacionadas como mínimo, por ejemplo:

- Solicitud: tiene un solicitante, un tipo, una descripción, un estado, y pertenece a una categoría.
- Categoría (u otra entidad relacionada 1:N o N:M con Solicitud, a tu elección).

El sistema debe tener dos roles de usuario:

- Solicitante: puede crear solicitudes y ver únicamente las propias.
- Revisor/Administrador: puede ver todas las solicitudes, filtrarlas, y cambiar su estado.

# 3\. Requerimientos funcionales

## 3.1 Backend (Laravel — API REST)

- CRUD de Solicitudes y de la entidad relacionada, con al menos una relación real (1:N o N:M) modelada en base de datos.
- Máquina de estados simple para la Solicitud (mínimo 3 estados, ej. pendiente → en revisión → aprobada/rechazada), validando que solo se permitan transiciones válidas (no se puede pasar de "pendiente" a "aprobada" directamente, por ejemplo).
- Al menos una validación de negocio no trivial que dependa de datos existentes (ej. no permitir aprobar una solicitud si le falta un campo obligatorio para su tipo, o si la categoría asociada está inactiva).
- Listado de solicitudes con paginación, al menos un filtro (ej. por estado o categoría) y ordenamiento por un campo.
- Uso de Form Requests para validación de entrada y API Resources para las respuestas (no exponer los modelos Eloquent directamente).
- Separar la lógica de negocio del controlador (capa de servicio, etc).
- Manejo de errores consistente: códigos HTTP correctos (422, 403, 404, etc.) y una estructura de error uniforme en las respuestas.

## 3.2 Autenticación y autorización

- Login con JWT.
- Middleware/policy que restrinja endpoints según el rol (un Solicitante no debe poder ver ni modificar solicitudes de otro usuario; solo el Revisor puede cambiar estados).

## 3.3 Frontend (Angular — SPA)

- Login contra la API y manejo del token (guardado, envío en headers, expiración/cierre de sesión).
- Guard de rutas según el rol del usuario autenticado.
- Formulario reactivo para crear una solicitud, con al menos una validación cruzada entre dos campos (ej. un campo solo es obligatorio si otro tiene cierto valor).
- Vista de listado consumiendo la paginación/filtros del backend.
- Manejo visible de estados de carga y de error (no solo el camino feliz — qué pasa si la API responde 422, 403 o 500).

## 3.4 Docker|

- Un docker-compose.yml que levante todo el stack (frontend, backend y base de datos) con un solo comando.
- Si algo del entorno no puede correr completamente en Docker por restricciones de tiempo, explícalo en el README en vez de omitirlo silenciosamente.

# 4\. Entregable: README de decisiones

Junto con el código, incluye un README breve que responda:

- ¿Qué decisiones de arquitectura tomaste y por qué (estructura de carpetas, patrón usado en el backend, manejo de estado en el frontend, etc.)?
- ¿Qué dejarías distinto si tuvieras más tiempo?
- ¿Qué le faltaría a esta solución para considerarse lista para producción?
- Instrucciones claras para levantar el proyecto (docker-compose up, variables de entorno necesarias, credenciales de prueba, etc.).

# 5\. Criterios de evaluación

| **Área**           | **Qué se evalúa**                                                                                       | **Peso aprox.** |
| ------------------ | ------------------------------------------------------------------------------------------------------- | --------------- |
| Backend (Laravel)  | Modelado de datos, reglas de negocio, validaciones, estructura del código (Requests/Resources/Services) | 30%             |
| Frontend (Angular) | Consumo de API, manejo de estados de carga/error, formularios reactivos, guards                         | 25%             |
| Auth y seguridad   | JWT, roles/permisos, protección de rutas y endpoints                                                    | 15%             |
| Docker             | docker-compose funcional, documentación de levantamiento                                                | 20%             |
| Criterio técnico   | README de decisiones y trade-offs, calidad de las respuestas a preguntas abiertas                       | 10%             |

# 6\. Entrega

Repositorio Git (público o con acceso otorgado) con el código, el docker-compose.yml y el README de decisiones. Indica el tiempo real que te tomó completar la prueba — no afecta la evaluación, es solo para calibrar futuras pruebas.