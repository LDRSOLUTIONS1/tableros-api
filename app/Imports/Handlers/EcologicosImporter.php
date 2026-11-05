<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\ExcelValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EcologicosImporter implements ExcelSourceImporter
{
    public function templateHeadings(): array
    {
        return [
            'tema',
            'ano',
            'mes',
            'entidad_federativa',
            'unidades_vehiculares_electricas',
            'unidades_vehiculares_hibridas_plugin1',
            'unidades_vehiculares_hibridas2',
            'mes_numero',
        ];
    }

    public function requiredHeadings(): array
    {
        return [
            'tema',
            'ano',
            'mes',
            'entidad_federativa',
            'unidades_vehiculares_electricas',
            'unidades_vehiculares_hibridas_plugin1',
            'unidades_vehiculares_hibridas2',
            'mes_numero',
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
                'tema' => $this->textOrNull(
                    $row['tema'] ?? null
                ),
                'anio' => $this->integerOrNull(
                    $row['ano'] ?? null
                ),
                'mes' => $this->textOrNull(
                    $row['mes'] ?? null
                ),
                'entidad_federativa' => $this->textOrNull(
                    $row['entidad_federativa'] ?? null
                ),
                'unidades_vehiculares_electricas' => $this->integerOrNull(
                    $row['unidades_vehiculares_electricas'] ?? null
                ),
                'unidades_vehiculares_hibridas_plugin' => $this->integerOrNull(
                    $row['unidades_vehiculares_hibridas_plugin1'] ?? null
                ),
                'unidades_vehiculares_hibridas' => $this->integerOrNull(
                    $row['unidades_vehiculares_hibridas2'] ?? null
                ),
                'numero_mes' => $this->integerOrNull(
                    $row['mes_numero'] ?? null
                ),
            ];

            $rules = [
                'tema'                                  => 'required|string|max:255',
                'anio'                                  => 'required|integer|min:1900|max:2100',
                'mes'                                   => 'required|string|max:30',
                'entidad_federativa'                    => 'required|string|max:100',
                'unidades_vehiculares_electricas'       => 'required|integer|min:0',
                'unidades_vehiculares_hibridas_plugin'  => 'required|integer|min:0',
                'unidades_vehiculares_hibridas'         => 'required|integer|min:0',
                'numero_mes'                            => 'required|integer|min:1|max:12',
            ];

            $attributes = [
                'anio'                                  => 'año',
                'numero_mes'                            => 'mes número',
                'entidad_federativa'                    => 'entidad federativa',
                'unidades_vehiculares_electricas'       => 'unidades vehiculares eléctricas',
                'unidades_vehiculares_hibridas_plugin'  => 'unidades vehiculares híbridas plugin',
                'unidades_vehiculares_hibridas'         => 'unidades vehiculares híbridas',
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

            $identity = [
                'tema' => $this->normalizeIdentityText(
                    $data['tema']
                ),
                'anio' => (int) $data['anio'],
                'numero_mes' => (int) $data['numero_mes'],
                'entidad_federativa' => $this->normalizeIdentityText(
                    $data['entidad_federativa']
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
            'tema',
            'anio',
            'mes',
            'entidad_federativa',
            'unidades_vehiculares_electricas',
            'unidades_vehiculares_hibridas_plugin',
            'unidades_vehiculares_hibridas',
            'numero_mes',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('ecologicos')->upsert(
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
