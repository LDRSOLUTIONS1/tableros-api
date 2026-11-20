---
name: crear-importador-excel
description: >-
  Usar cuando el usuario solicite agregar una nueva fuente de Excel, dataset o
  importador de archivos .xlsx/.xls/.csv en tableros-api. Cubre migración, modelo,
  handler ExcelSourceImporter, registro en config y seeder.
---

# Crear una Nueva Fuente / Importador Excel en `tableros-api`

Una fuente de importación Excel en `tableros-api` se compone de **5 piezas obligatorias** vinculadas por un `CODIGO` único en **MAYÚSCULAS** (ej. `PRODUCCION`, `VENTAS_LIGEROS`):

1. **Migración de tabla dataset** (`database/migrations/...`)
2. **Modelo Eloquent del dataset** (`app/Models/<Dataset>.php`)
3. **Handler Importer** (`app/Imports/Handlers/<Dataset>Importer.php`) implementando `App\Contracts\ExcelSourceImporter`
4. **Registro dual en config**: `config/excel_importers.php` **y** `config/excel_datasets.php`
5. **Registro en BD**: `database/seeders/ExcelSourcesSeeder.php` (y opcionalmente asociación a página en `PowerBiDashboardStructureSeeder.php`)

> **Importante**: no se requiere crear rutas ni controladores nuevos. `ExcelImportController` y `ImportacionesController` resuelven automáticamente subida (`/importaciones/{excelSource}/subir`), descarga de layout (`/importaciones/{excelSource}/layout`), vista de datos paginados (`/{excelSource}/datos`) e historial de versiones (`/{excelSource}/versiones`) a partir del `codigo` de la fuente.

---

## Paso 1 — Migración del Dataset
Toda tabla destino de Excel **debe** incluir:
```php
$table->id();
$table->foreignId('excel_import_version_id')
    ->constrained('excel_import_versions')
    ->cascadeOnDelete();

$table->char('registro_hash', 64)
    ->unique('<tabla>_registro_hash_unique');

// Columnas de negocio del dataset...
$table->timestamps();
```
- `registro_hash` permite `upsert` idempotente sin duplicar registros entre versiones.
- Elegir tipos exactos (`unsignedSmallInteger`, `decimal(14,2)`, `unsignedBigInteger`, etc.) y longitudes acordes a las reglas de validación.

## Paso 2 — Modelo Eloquent (`app/Models/<Dataset>.php`)
- Definir `protected $table = '<tabla>';`.
- Definir `$fillable` con **todas las columnas del dataset** en el orden en que deben mostrarse en la tabla del frontend (además de `excel_import_version_id` y `registro_hash`, los cuales `ExcelImportController::datosActuales` oculta automáticamente).
- Declarar relación `version(): BelongsTo` hacia `ExcelImportVersion::class` (`excel_import_version_id`).

## Paso 3 — Handler (`app/Imports/Handlers/<Dataset>Importer.php`)
Implementar `App\Contracts\ExcelSourceImporter` (tomar como referencia `app/Imports/Handlers/ProduccionImporter.php`):

1. **`templateHeadings(): array`**: encabezados exactos que se descargan en el layout `.xlsx`.
2. **`requiredHeadings(): array`**: encabezados obligatorios que `ExcelImportController` valida antes de llamar a `import()`. Deben coincidir con las claves normalizadas que genera `ExcelRowsReader` (minúsculas/slug sin acentos según `maatwebsite/excel`).
3. **`import(Collection $rows, int $versionId): int`**:
   - Recorrer `$rows` ignorando filas completamente vacías.
   - Normalizar cada celda con helpers privados (`textOrNull`, `integerOrNull`, `decimalOrNull`, `dateOrNull`).
   - Validar fila por fila usando `Validator::make($data, $rules, ExcelValidation::messages(), $attributes)`.
   - Si falla, lanzar `ValidationException::withMessages(['archivo' => ['Error en la fila Excel ' . ($index + 2) . ': ' . implode(' ', $validator->errors()->all())]])`.
   - Calcular `$registroHash` (`hash('sha256', json_encode($identity, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))`) a partir de los campos clave normalizados (`normalizeIdentityText`: trim, colapsar espacios, `mb_strtoupper`).
   - Agregar `registro_hash`, `excel_import_version_id`, `created_at`, `updated_at`.
   - Si `$records` queda vacío, lanzar `ValidationException`.
   - Insertar/actualizar en lotes de 500:
     ```php
     foreach (array_chunk($records, 500) as $chunk) {
         DB::table('<tabla>')->upsert($chunk, ['registro_hash'], $updatedColumns);
     }
     ```
   - Retornar `count($records)`.
   - **Nota**: si el Excel puede traer filas duplicadas dentro del mismo archivo con el mismo `registro_hash`, deduplicar/sobrescribir por `$records[$registroHash]` antes del `upsert` para evitar colisiones dentro del mismo lote.

## Paso 4 — Registrar en Ambos Archivos de Config
1. **`config/excel_importers.php`**:
   ```php
   'MI_CODIGO' => \App\Imports\Handlers\MiDatasetImporter::class,
   ```
2. **`config/excel_datasets.php`**:
   ```php
   'MI_CODIGO' => [
       'model' => \App\Models\MiDataset::class,
       'label' => 'Nombre legible del dataset',
   ],
   ```
   (Opcional: agregar `'columns' => [...]` si se desea un subconjunto/orden específico distinto a `$fillable`).

## Paso 5 — Seeder y Asociación
1. Agregar el registro (`codigo`, `nombre`, `descripcion`) en `database/seeders/ExcelSourcesSeeder.php` (usa `updateOrInsert` con `estado => 2`).
2. Si pertenece a una página de tablero, registrarlo en `PowerBiDashboardStructureSeeder.php` o asociarlo vía UI / `POST /api/paginas/{id}/excels`.

## Paso 6 — Verificación
- Correr migraciones y seeder:
  ```sh
  php artisan migrate
  php artisan db:seed --class=ExcelSourcesSeeder
  ```
- Probar descarga de layout (`GET /api/importaciones/{id}/layout`), subida de archivo válido (`201`), archivo duplicado (`409`), encabezados faltantes (`422`), fila inválida (`422`) y consulta de datos (`GET /api/{id}/datos`).
- Ejecutar `./vendor/bin/pint --test` y `php artisan test`.
