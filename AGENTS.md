# tableros-api — Guía de Proyecto (Laravel 13)

API REST para gestión de Tableros Power BI, usuarios/roles/logs y pipeline versionado de importación de archivos Excel.

## Stack Tecnológico
- **Runtime**: PHP `^8.3`, Laravel `^13.17` (`bootstrap/app.php` minimalista sin `Http/Kernel.php`).
- **Auth**: Laravel Sanctum `^4.0` (Bearer tokens) + `firebase/php-jwt` `^7.2` (SSO desde intranet RH vía cookie `token`).
- **Excel**: `maatwebsite/excel` `^4.0` + `App\Support\XlsxTrailingRowsCleaner`.
- **Calidad**: Laravel Pint (`vendor/bin/pint`) y PHPUnit 12 (`php artisan test`).

## Estructura del Proyecto
```text
app/
├── Contracts/ExcelSourceImporter.php   # Contrato obligatorio para handlers Excel
├── Exports/ExcelSourceLayoutExport.php # Genera plantilla .xlsx descargable por fuente
├── Http/Controllers/                   # Controladores REST (Auth, Categories, Tableros, ExcelSourcePage, Importaciones, ExcelImport, Users, Roles, Logs)
├── Imports/
│   ├── ExcelRowsReader.php             # Lector genérico por encabezados
│   └── Handlers/*Importer.php          # 12 importadores por código de fuente
├── Models/                             # Modelos administrativos + 12 modelos de datasets Excel
└── Support/
    ├── ExcelValidation.php             # Mensajes estándar en español para validación de filas Excel
    └── XlsxTrailingRowsCleaner.php     # Limpia filas vacías al final de .xlsx antes de importar
config/
├── excel_importers.php                 # Mapa CODIGO => Handler::class
├── excel_datasets.php                  # Mapa CODIGO => ['model' => Model::class, 'label' => '...']
└── jwt.php                             # Secretos JWT externos (jwt.secret_rh)
routes/api.php                          # Todas las rutas bajo prefijo /api y middleware auth:sanctum
database/migrations/ & seeders/         # Migraciones base + 12 tablas dataset + seeders
```

## Mapa de Endpoints Existentes (`routes/api.php`)
- **Públicos**: `POST /api/login`, `POST /api/login/{collaborator_number}`.
- **Autenticados (`auth:sanctum`)**:
  - `GET /api/user`
  - Recursos CRUD (`index`, `store`, `show`, `update`): `/roles`, `/usuarios`, `/logs`, `/categorias`, `/tableros`, `/paginas`, `/importaciones`
  - Especiales: `GET /api/indexLimited`, `GET /api/indexName`, `POST /api/dashboard/assign`, `POST /api/paginas/{id}/excels`, `GET /api/importacionesPorTablero`
  - Pipeline Excel: `POST /api/importaciones/{excelSource}/subir`, `GET /api/importaciones/{excelSource:id}/layout`, `GET /api/{excelSource}/datos`, `GET /api/{excelSource}/versiones`, `GET /api/importaciones/{excelSource}/versiones/{version}/descargar`

## Convenciones Obligatorias del Proyecto
- **Borrado lógico (`estado`)**: `tinyInteger` default `2` (`0=Eliminado`, `1=Inactivo`, `2=Activo`). Todo modelo administrativo implementa `scopeActivos($query)` retornando `$query->whereIn('estado', [1, 2])`. Para operaciones activas (subir Excel, tableros visibles), validar `estado === 2`.
- **Roles (`role_id`)**: `1` Super Administrador, `2` Administrador, `3` Limitado (solo `dashboard_user`), `4` Consultor (solo lectura).
- **Respuestas JSON**:
  - `index()` / `show()`: devuelven el modelo o colección JSON directamente (`200`).
  - `store()` (`201`) / `update()` (`200`): `['message' => '... correctamente', 'data' => $modelo]`.
  - Errores de validación y mensajes al usuario siempre en **español**.
- **Consultas eficientes**: usar `select(...)` explícito y `with('relacion:id,campo')` para evitar N+1 y sobreexposición de columnas.
- **No incluir `created_at`/`updated_at` en `$fillable`** en modelos nuevos; definir `casts(): array` en Laravel 13.

## Qué Compone un Módulo Nuevo (Guías Detalladas en `.agents/`)
Antes de crear un módulo o importador nuevo, leer la skill correspondiente (carga progresiva para ahorrar tokens):
- **Nuevo módulo CRUD API**: [crear-modulo-api](.agents/skills/crear-modulo-api/SKILL.md) (Migración → Modelo → FormRequest → Controlador → Rutas → Seeder → Feature Test).
- **Nueva fuente / importador Excel**: [crear-importador-excel](.agents/skills/crear-importador-excel/SKILL.md) (Migración con `excel_import_version_id` y `registro_hash` → Modelo → Handler `ExcelSourceImporter` → `config/excel_importers.php` + `config/excel_datasets.php` → `ExcelSourcesSeeder`).
- **Reglas detalladas y mejores prácticas**: [arquitectura-y-practicas.md](.agents/rules/arquitectura-y-practicas.md).

## Verificación
```sh
php artisan test
./vendor/bin/pint --test
```
