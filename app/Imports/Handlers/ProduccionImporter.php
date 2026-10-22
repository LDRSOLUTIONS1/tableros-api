<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProduccionImporter implements ExcelSourceImporter
{
    public function requiredHeadings(): array
    {
        return [
            'prod_est',
            'cobertura',
            'ano',
            'mes',
            'marca',
            'modelo',
            'tipo',
            'segmento',
            'produccion',
            'numero_mes',
        ];
    }

    public function import(Collection $rows, int $versionId): int
    {
        $records = [];
        $now = now()->toDateTimeString();

        foreach ($rows as $index => $row) {
            $row = collect($row)->toArray();

            $hasData = collect($row)->contains(
                fn($value) => $value !== null
                    && trim((string) $value) !== ''
            );

            if (!$hasData) {
                continue;
            }
            // Preparar los datos del Excel.
            $data = [
                'prod_est' => $this->textOrNull(
                    $row['prod_est'] ?? null
                ),
                'cobertura' => $this->textOrNull(
                    $row['cobertura'] ?? null
                ),
                'anio' => $this->integerOrNull(
                    $row['ano'] ?? null
                ),
                'mes' => $this->textOrNull(
                    $row['mes'] ?? null
                ),
                'marca' => $this->textOrNull(
                    $row['marca'] ?? null
                ),
                'modelo' => $this->textOrNull(
                    $row['modelo'] ?? null
                ),
                'tipo' => $this->textOrNull(
                    $row['tipo'] ?? null
                ),
                'segmento' => $this->textOrNull(
                    $row['segmento'] ?? null
                ),
                'produccion' => $this->integerOrNull(
                    $row['produccion'] ?? null
                ),
                'numero_mes' => $this->integerOrNull(
                    $row['numero_mes'] ?? null
                ),
            ];

            $validator = Validator::make($data, [
                'prod_est'      => 'nullable|string|max:255',
                'cobertura'     => 'nullable|string|max:100',
                'anio'          => 'required|integer|min:1900|max:2100',
                'mes'           => 'required|string|max:30',
                'marca'         => 'nullable|string|max:100',
                'modelo'        => 'nullable|string|max:150',
                'tipo'          => 'nullable|string|max:100',
                'segmento'      => 'nullable|string|max:100',
                'produccion'    => 'required|integer|min:0',
                'numero_mes'    => 'required|integer|min:1|max:12',
            ], [
                'prod_est.required'      => 'El campo prod_est es requerido.',
                'cobertura.required'     => 'El campo cobertura es requerido.',
                'anio.required'          => 'El campo anio es requerido.',
                'mes.required'           => 'El campo mes es requerido.',
                'marca.required'         => 'El campo marca es requerido.',
                'modelo.required'        => 'El campo modelo es requerido.',
                'tipo.required'          => 'El campo tipo es requerido.',
                'segmento.required'      => 'El campo segmento es requerido.',
                'produccion.required'    => 'El campo produccion es requerido.',
                'numero_mes.required'    => 'El campo numero_mes es requerido.',
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'archivo' => [
                        'Error en la fila Excel '
                            . ($index + 2)
                            . ': '
                            . implode(' ', $validator->errors()->all()),
                    ],
                ]);
            }

            $data = $validator->validated();

            $identity = [
                'prod_est' => $this->normalizeIdentityText(
                    $data['prod_est'] ?? null
                ),
                'cobertura' => $this->normalizeIdentityText(
                    $data['cobertura'] ?? null
                ),
                'anio' => (int) $data['anio'],
                'numero_mes' => (int) $data['numero_mes'],
                'marca' => $this->normalizeIdentityText(
                    $data['marca'] ?? null
                ),
                'modelo' => $this->normalizeIdentityText(
                    $data['modelo'] ?? null
                ),
                'tipo' => $this->normalizeIdentityText(
                    $data['tipo'] ?? null
                ),
                'segmento' => $this->normalizeIdentityText(
                    $data['segmento'] ?? null
                ),
            ];

            $registroHash = hash(
                'sha256',
                json_encode(
                    $identity,
                    JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                        | JSON_THROW_ON_ERROR
                )
            );

            $records[] = array_merge($data, [
                'registro_hash' => $registroHash,
                'excel_import_version_id' => $versionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (empty($records)) {
            throw ValidationException::withMessages([
                'archivo' => [
                    'El archivo no contiene registros válidos.',
                ],
            ]);
        }

        $updatedColumns = [
            'prod_est',
            'cobertura',
            'anio',
            'mes',
            'marca',
            'modelo',
            'tipo',
            'segmento',
            'produccion',
            'numero_mes',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('produccion_vehiculos')->upsert(
                $chunk,
                ['registro_hash'],
                $updatedColumns
            );
        }

        return count($records);
    }

    private function normalizeIdentityText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace(
            '/\s+/u',
            ' ',
            trim((string) $value)
        );

        if ($value === '') {
            return null;
        }

        return mb_strtoupper($value, 'UTF-8');
    }

    private function textOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function integerOrNull(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (
            !is_numeric($value)
            || (float) $value != (int) $value
        ) {
            return null;
        }

        return (int) $value;
    }
}
