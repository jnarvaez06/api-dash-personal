---
name: documentar-endpoint
description: Genera/actualiza documentación Scribe (atributos PHP + bodyParameters) para los endpoints de la API con cambios en staging. Úsalo cuando el usuario diga "documentar", "documentación", "doc scribe", "genera documentación" o "actualiza la doc".
---

## 1. Revisar el staging

Ejecuta `git diff --cached --name-status`.

- Si no hay archivos en staging → responde que no hay cambios para documentar y detente. No mires el working tree sin stage ni el historial de commits.
- Si hay archivos, filtra los relevantes para la API:
  - `app/Http/Controllers/Api/**`
  - `app/Http/Requests/**`
  - `app/Http/Resources/**`
  - `routes/api.php` (o donde estén las rutas API)

Si ninguno de los archivos en staging cae en esas rutas, avisa que no hay cambios de API para documentar y detente.

## 2. Detectar qué documentar

Con `git diff --cached` sobre esos archivos, identifica:

- Métodos públicos nuevos en un controller de `Api/` → nuevo endpoint sin `#[Endpoint]`.
- Controllers nuevos → sin `#[Group]`.
- Métodos existentes modificados (cambia la respuesta, la validación, etc.) → revisar si el `#[Response]` sigue siendo fiel a lo que devuelve el código.
- FormRequests nuevos o con `rules()` modificado → revisar si `bodyParameters()` existe y está sincronizado con `rules()`.

## 3. Documentar siguiendo el estilo existente del proyecto

Mira `app/Http/Controllers/Api/AccountController.php` y `app/Http/Controllers/Api/AuthController.php` como referencia de estilo antes de escribir nada nuevo.

Para cada controller de `Api/`:

- Clase: `#[Group('Nombre', 'Descripción corta en español.')]`.
- Cada acción pública:
  - `#[Endpoint('Título en inglés', 'Descripción en español de qué hace.')]`.
  - `#[Unauthenticated]` si la ruta no pasa por `auth:sanctum`.
  - `#[Response(status: 200|201, content: [...])]` con el shape real de respuesta (`success`, `message`, `data`), usando valores de ejemplo realistas y coherentes con lo que el método realmente retorna — no placeholders genéricos.
  - `#[Response(status: 404|422|401, content: [...], description: '...')]` por cada error de negocio real que devuelve el método (validación, not found, credenciales inválidas, etc.). No inventes casos que el código no contempla.

Para cada FormRequest:

- Verifica/crea `bodyParameters()` con `description` + `example` por cada campo de `rules()`, en el mismo estilo que `StoreAccountRequest` / `ChangePasswordRequest` (un array asociativo campo → `['description' => ..., 'example' => ...]`).

No modifiques lógica de negocio — solo agrega o ajusta atributos de documentación y `bodyParameters()`.

## 4. Regenerar la documentación

Pregunta antes de ejecutar (a menos que el usuario ya lo haya pedido explícitamente en el mismo mensaje):

```bash
php artisan scribe:generate
```

Los archivos generados no se versionan (ver `.gitignore`), así que es una operación de bajo riesgo, pero igual confirma si no fue pedido de forma explícita.

## 5. Resumen final

Lista qué endpoints y FormRequests se documentaron o actualizaron, y si se regeneró la documentación.
