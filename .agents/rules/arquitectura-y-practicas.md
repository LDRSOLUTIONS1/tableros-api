---
trigger: model_decision
description: >-
  Consultar al modificar controladores, modelos, migraciones, permisos, validaciones
  o endpoints de importación Excel en tableros-api para respetar contratos, seguridad y rendimiento.
---

# Arquitectura y Mejores Prácticas — `tableros-api`

## 1. Arquitectura Laravel 13
- Configuración de rutas, middlewares y manejo global de excepciones en `bootstrap/app.php` (no existen `app/Http/Kernel.php` ni `app/Exceptions/Handler.php`).
- Excepciones `AuthenticationException` retornan siempre JSON `401` (`['message' => 'No autorizado']`).
- Todas las rutas protegidas van dentro de `Route::middleware('auth:sanctum')->group(...)` en `routes/api.php`.

## 2. Convenciones de Base de Datos y Modelos
- **Borrado lógico obligatorio**: usar columna `$table->tinyInteger('estado')->default(2)->comment('0=Eliminado, 1=Inactivo, 2=Activo')`. Nunca eliminar registros administrativos físicamente (`DELETE`) salvo pivotes (`sync`).
- **Scope estándar**: todo modelo administrativo debe declarar:
  ```php
  public function scopeActivos($query)
  {
      return $query->whereIn('estado', [1, 2]);
  }
  ```
- **Casts modernos (Laravel 13)**: usar método `protected function casts(): array` (como en `PowerBiDashboardPage`).
- **`$fillable` limpio**: en modelos nuevos NO incluir `created_at` ni `updated_at` en `$fillable`.
- **Relaciones tipadas**: declarar tipo de retorno explícito (`BelongsTo`, `HasMany`, `BelongsToMany`) y llaves foráneas explícitas cuando el nombre no sea el convencional.
- **Migraciones**: definir índices en columnas usadas en `WHERE`/`ORDER BY`/FKs y asegurar que `down()` sea reversible.

## 3. Controladores, Validación y Autorización
- **Validación**: en módulos nuevos preferir **FormRequest** (`app/Http/Requests/<Modulo>/Store...Request.php` y `Update...Request.php`) con mensajes personalizados en **español** y `Rule::unique(...)->ignore($id)` en lugar de concatenación de strings. Si se mantiene método privado en el controlador, seguir la misma regla `Rule::unique`.
- **Autorización por rol**: validar explícitamente en acciones sensibles (crear, actualizar, asignar, eliminar lógico). Ejemplo estándar del proyecto:
  ```php
  abort_unless(
      in_array((int) $request->user()->role_id, [1, 2], true),
      403,
      'No tienes permisos para realizar esta acción.'
  );
  ```
  > Nota: los roles se identifican por `role_id` (`1` Super Admin, `2` Admin, `3` Limitado, `4` Consultor). En acciones solo de Super Admin (Usuarios, Roles, Logs), restringir a `[1]`. Para Consultor (`4`), solo lectura (`GET`).
- **Contratos HTTP compatibles con `tableros-front`**:
  - `GET /recurso` → `response()->json($coleccion, 200)` (array directo ordenado `orderBy('id', 'desc')`).
  - `GET /recurso/{id}` → `response()->json($item, 200)` usando `activos()->firstOrFail()`.
  - `POST /recurso` → `response()->json(['message' => '... creado correctamente', 'data' => $item], 201)`.
  - `PUT /recurso/{id}` → `response()->json(['message' => '... actualizado correctamente', 'data' => $item], 200)`.
  - Validar `estado` siempre como `nullable|integer|in:0,1,2` (evitar `in:1,2,3` heredado de código antiguo).
- **Transacciones**: envolver en `DB::transaction(...)` toda operación multi-tabla (`sync`, importaciones, creación con dependencias) y usar `lockForUpdate()` ante condiciones de carrera (ver `ExcelSourcePageController::assignExcels` y `ExcelImportController::store`).
- **Prevención de N+1**: siempre cargar relaciones con `with(['relacion:id,campo'])` y limitar columnas con `select(...)`.

## 4. Pipeline de Importaciones Excel
- `ExcelImportController::store` garantiza:
  1. Verificación de fuente activa (`estado === 2`) y existencia de handler registrado en `config/excel_importers.php`.
  2. Deduplicación por `sha256` del archivo (`file_hash`) con `409 Conflict` si ya fue subido a la misma fuente.
  3. Registro previo de `ExcelImportVersion` en estado `processing` (preserva historial incluso en fallo `failed`).
  4. Limpieza de filas vacías finales en `.xlsx` vía `XlsxTrailingRowsCleaner`.
  5. Validación de `requiredHeadings()` antes de abrir transacción de escritura.
  6. Ejecución de `$handler->import($rows, $version->id)` y actualización de `current_version_id` dentro de una misma `DB::transaction`.
- `ExcelImportController::datosActuales` lee el modelo desde `config/excel_datasets.php` y excluye automáticamente `excel_import_version_id` y `registro_hash` de las columnas expuestas.

## 5. Estilo y Pruebas
- Formatear siempre con `./vendor/bin/pint` (o verificar con `./vendor/bin/pint --test`).
- Escribir al menos un Feature Test en `tests/Feature/` por módulo nuevo cubriendo autenticación (`401`), permisos (`403`), validación (`422`) y CRUD exitoso (`200`/`201`).
