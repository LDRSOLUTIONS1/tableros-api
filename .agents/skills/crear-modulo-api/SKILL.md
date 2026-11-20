---
name: crear-modulo-api
description: >-
  Usar cuando el usuario solicite crear un nuevo módulo, entidad o recurso CRUD API
  en tableros-api (Laravel 13). Contiene la composición exacta, orden de archivos y
  mejores prácticas del proyecto.
---

# Crear un Nuevo Módulo CRUD en `tableros-api`

Un módulo administrativo completo en `tableros-api` se compone de **6 piezas**:
1. **Migración** (`database/migrations/...`)
2. **Modelo Eloquent** (`app/Models/<Entidad>.php`)
3. **FormRequests** (`app/Http/Requests/<Entidad>/Store...Request.php` y `Update...Request.php`)
4. **Controlador REST** (`app/Http/Controllers/<Modulo>Controller.php`)
5. **Rutas API** (`routes/api.php`)
6. **Seeder (opcional) + Feature Test** (`tests/Feature/<Modulo>Test.php`)

---

## Paso 1 — Migración
Crear con `php artisan make:migration create_<tabla>_table` (o archivo en `database/migrations/`).

Reglas:
- Incluir llaves foráneas con `foreignId(...)->constrained(...)`.
- Incluir borrado lógico estándar:
  ```php
  $table->tinyInteger('estado')
      ->default(2)
      ->comment('0=Eliminado, 1=Inactivo, 2=Activo');
  $table->timestamps();
  ```
- Agregar índices en FKs y columnas de búsqueda/orden frecuente.

## Paso 2 — Modelo (`app/Models/<Entidad>.php`)
- Definir `$table` explícito si el nombre no sigue pluralización inglesa estándar.
- Definir `$fillable` **sin** `created_at` ni `updated_at`.
- Definir `protected function casts(): array` con `'estado' => 'integer'` y tipos propios.
- Declarar relaciones con tipos de retorno (`BelongsTo`, `HasMany`, `BelongsToMany`).
- Declarar el scope obligatorio:
  ```php
  public function scopeActivos($query)
  {
      return $query->whereIn('estado', [1, 2]);
  }
  ```

## Paso 3 — Validación y Autorización (`app/Http/Requests/<Modulo>/`)
Crear `Store<Entidad>Request` y `Update<Entidad>Request`:
- `authorize()`: validar roles permitidos (ej. `in_array((int) $this->user()?->role_id, [1, 2], true)` para escritura, o `[1]` si es administración de sistema).
- `rules()`: usar `Rule::unique('<tabla>', 'nombre')->ignore($this->route('<param>'))` en updates. Validar `'estado' => ['nullable', 'integer', 'in:0,1,2']`.
- `messages()`: todos los mensajes en **español** claros para mostrar directo en SweetAlert2 del frontend.

## Paso 4 — Controlador (`app/Http/Controllers/<Modulo>Controller.php`)
Implementar los 4 métodos estándar (sin `destroy` físico; el borrado lógico se hace vía `update` enviando `estado = 0`):
- `index()`: `with(...)` con columnas mínimas + `select(...)` explícito + `activos()` + `orderBy('id', 'desc')->get()`. Retornar `response()->json($items, 200)`.
  - Si aplica restricción por rol `Limitado` (`role_id === 3`), filtrar por relación con `$request->user()` (ver `TablerosController::index`).
- `store(Store<Entidad>Request $request)`: crear con `$request->validated()` y retornar `['message' => '<Entidad> creado correctamente', 'data' => $item]` con HTTP `201`.
- `show($id)`: `with(...)` + `select(...)` + `where('id', $id)->activos()->firstOrFail()`. Retornar `response()->json($item, 200)`.
- `update(Update<Entidad>Request $request, $id)`: buscar con `activos()->findOrFail($id)`, actualizar con `$request->validated()` y retornar `['message' => '<Entidad> actualizado correctamente', 'data' => $item]` con HTTP `200`.
- Si una acción modifica múltiples tablas o relaciones pivote (`sync`), envolver en `DB::transaction(...)` con `lockForUpdate()`.

## Paso 5 — Rutas (`routes/api.php`)
Registrar dentro de `Route::middleware('auth:sanctum')->group(function () { ... })`:
```php
Route::apiResource('/<recurso>', <Modulo>Controller::class)->except(['destroy']);
```
- Usar nombres de ruta en **español, minúsculas y plural** (coherente con `/categorias`, `/tableros`, `/paginas`, `/usuarios`, `/roles`, `/importaciones`).
- Si hay rutas custom sobre el recurso, colocarlas en orden correcto para no colisionar con `{id}` (ej. rutas estáticas antes que dinámicas, o usar `whereNumber(...)`).

## Paso 6 — Seeder (si requiere catálogo inicial)
- Usar `updateOrInsert` o `firstOrCreate` dentro de `DB::transaction` para garantizar idempotencia.
- Registrar en `database/seeders/DatabaseSeeder.php`.

## Paso 7 — Pruebas y Verificación
1. Crear `tests/Feature/<Modulo>Test.php` validando:
   - Acceso sin token → `401`.
   - Rol sin permiso en `store`/`update` (ej. Consultor `role_id = 4`) → `403`.
   - Payload inválido → `422` con mensajes en español.
   - Flujo completo `POST` (`201`) → `GET` listado (`200`) → `GET` detalle (`200`) → `PUT` (`200`) → borrado lógico `estado = 0` ya no aparece en `GET`.
2. Ejecutar verificación:
   ```sh
   php artisan migrate
   php artisan test --filter=<Modulo>Test
   ./vendor/bin/pint --test
   ```
