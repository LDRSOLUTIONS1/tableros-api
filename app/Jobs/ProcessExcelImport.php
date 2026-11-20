<?php

namespace App\Jobs;

use App\Models\ExcelImportVersion;
use App\Models\ExcelSource;
use App\Imports\ExcelRowsReader;
use App\Support\XlsxTrailingRowsCleaner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProcessExcelImport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public int $versionId,
        public string $handlerClass,
        public string $extension
    ) {
    }

    public function handle(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        $version = ExcelImportVersion::find($this->versionId);

        if (!$version || $version->status !== 'processing') {
            return;
        }

        $tempImportPath = null;

        try {
            $originalPath = Storage::disk('local')->path(
                $version->file_path
            );

            $importPath = $originalPath;

            if (strtolower($this->extension) === 'xlsx') {
                $tempImportPath = app(XlsxTrailingRowsCleaner::class)
                    ->cleanCopy($originalPath);

                $importPath = $tempImportPath;
            }

            $reader = new ExcelRowsReader();

            Excel::import($reader, $importPath);

            $rows = $reader->rows
                ->filter(function ($row) {
                    return collect($row)->contains(
                        fn($value) => $value !== null
                            && trim((string) $value) !== ''
                    );
                })
                ->values();

            // Liberar memoria del lector.
            $reader->rows = collect();

            if ($rows->isEmpty()) {
                throw ValidationException::withMessages([
                    'archivo' => ['El archivo no contiene registros.'],
                ]);
            }

            $handler = app($this->handlerClass);
            $firstRow = collect($rows->first())->toArray();

            $missing = array_diff(
                $handler->requiredHeadings(),
                array_keys($firstRow)
            );

            if (!empty($missing)) {
                throw ValidationException::withMessages([
                    'archivo' => [
                        'Faltan las columnas requeridas: '
                            . implode(', ', $missing),
                    ],
                ]);
            }

            DB::transaction(function () use ($handler, $rows, $version) {
                $total = $handler->import($rows, $version->id);

                $version->update([
                    'status' => 'completed',
                    'total_rows' => $total,
                    'imported_at' => now(),
                    'error_message' => null,
                ]);

                $source = ExcelSource::whereKey($version->excel_source_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentVersionNumber = 0;

                if ($source->current_version_id) {
                    $currentVersionNumber = (int) ExcelImportVersion::whereKey(
                        $source->current_version_id
                    )->value('version_number');
                }

                // Evita que una importación anterior que termina tarde
                // reemplace una versión más reciente.
                if ($version->version_number > $currentVersionNumber) {
                    $source->update([
                        'current_version_id' => $version->id,
                    ]);
                }
            });
        } catch (ValidationException $e) {
            $this->markFailed(
                $version,
                implode(' ', collect($e->errors())->flatten()->all())
            );
        } catch (Throwable $e) {
            report($e);

            $this->markFailed($version, $e->getMessage());
        } finally {
            if ($tempImportPath && is_file($tempImportPath)) {
                @unlink($tempImportPath);
            }
        }
    }

    /**
     * Se ejecuta si el worker mata el job (timeout, memoria, etc.).
     */
    public function failed(?Throwable $exception): void
    {
        $version = ExcelImportVersion::find($this->versionId);

        if ($version && $version->status === 'processing') {
            $this->markFailed(
                $version,
                $exception?->getMessage() ?? 'La importación fue interrumpida.'
            );
        }
    }

    private function markFailed(ExcelImportVersion $version, string $message): void
    {
        $version->update([
            'status' => 'failed',
            'error_message' => mb_substr($message, 0, 60000),
        ]);
    }
}
