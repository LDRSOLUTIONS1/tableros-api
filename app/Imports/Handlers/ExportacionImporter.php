<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\ExcelValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExportacionImporter implements ExcelSourceImporter
{
    public function templateHeadings(): array
    {
        return [
            'prod_est',
            'cobertura',
            'ano',
            'mes_numero',
            'mes',
            'marca',
            'modelo',
            'tipo',
            'segmento',
            'id_pais_destino',
            'exportacion',
            'estatus',
            'pais_destino',
        ];
    }
    public function requiredHeadings(): array
    {
        return [
            'prod_est',
            'cobertura',
            'ano',
            'mes_numero',
            'mes',
            'marca',
            'modelo',
            'tipo',
            'segmento',
            'id_pais_destino',
            'exportacion',
            'estatus',
            'pais_destino',
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
                'mes_numero' => $this->integerOrNull(
                    $row['mes_numero'] ?? null
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
                'id_pais_destino' => $this->integerOrNull(
                    $row['id_pais_destino'] ?? null
                ),
                'exportacion' => $this->integerOrNull(
                    $row['exportacion'] ?? null
                ),
                'estatus' => $this->textOrNull(
                    $row['estatus'] ?? null
                ),
                'pais_destino' => $this->textOrNull(
                    $row['pais_destino'] ?? null
                ),
            ];

            $rules = [
                'prod_est'          => 'nullable|string|max:255',
                'cobertura'         => 'nullable|string|max:100',
                'anio'              => 'required|integer|min:1900|max:2100',
                'mes_numero'        => 'nullable|integer|between:1,12',
                'mes'               => 'required|string|max:30',
                'marca'             => 'nullable|string|max:100',
                'modelo'            => 'nullable|string|max:150',
                'tipo'              => 'nullable|string|max:100',
                'segmento'          => 'nullable|string|max:100',
                'id_pais_destino'   => 'nullable|integer|min:0',
                'exportacion'       => 'nullable|integer|min:0',
                'estatus'           => 'nullable|string|max:100',
                'pais_destino'      => 'nullable|string|max:100',
            ];

            $attributes = [
                'prod_est'          => 'prod_est',
                'cobertura'         => 'cobertura',
                'anio'              => 'año',
                'mes_numero'        => 'mes número',
                'mes'               => 'mes',
                'marca'             => 'marca',
                'modelo'            => 'modelo',
                'tipo'              => 'tipo',
                'segmento'          => 'segmento',
                'id_pais_destino'   => 'id país de destino',
                'exportacion'       => 'exportación',
                'estatus'           => 'estatus',
                'pais_destino'      => 'país de destino',
            ];

            $validator = Validator::make(
                $data,
                $rules,
                ExcelValidation::messages(),
                $attributes
            );

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

            $data['exportacion'] = (int) ($data['exportacion'] ?? 0);

            $identity = [
                'prod_est' => $this->normalizeIdentityText(
                    $data['prod_est'] ?? null
                ),
                'cobertura' => $this->normalizeIdentityText(
                    $data['cobertura'] ?? null
                ),
                'anio' => (int) $data['anio'],
                'mes_numero' => isset($data['mes_numero'])
                    ? (int) $data['mes_numero']
                    : null,
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
                'id_pais_destino' => isset($data['id_pais_destino'])
                    ? (int) $data['id_pais_destino']
                    : null,
                'exportacion' => (int) $data['exportacion'],
                'estatus' => $this->normalizeIdentityText(
                    $data['estatus'] ?? null
                ),
                'pais_destino' => $this->normalizeIdentityText(
                    $data['pais_destino'] ?? null
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
            'mes_numero',
            'mes',
            'marca',
            'modelo',
            'tipo',
            'segmento',
            'exportacion',
            'pais_destino',
            'id_pais_destino',
            'estatus',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('exportacion_vehiculos')->upsert(
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
