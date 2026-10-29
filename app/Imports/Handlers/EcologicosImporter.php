<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EcologicosImporter implements ExcelSourceImporter
{
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

            $validator = Validator::make($data, [
                'tema'                                  => 'required|string|max:255',
                'anio'                                  => 'required|integer|min:1900|max:2100',
                'mes'                                   => 'required|string|max:30',
                'entidad_federativa'                    => 'required|string|max:100',
                'unidades_vehiculares_electricas'       => 'required|integer|min:0',
                'unidades_vehiculares_hibridas_plugin'  => 'required|integer|min:0',
                'unidades_vehiculares_hibridas'         => 'required|integer|min:0',
                'numero_mes'                            => 'required|integer|min:1|max:12',
            ], [
                'tema.required'                                 => 'El campo tema es requerido.',
                'tema.max'                                      => 'El campo tema no puede exceder 255 caracteres.',
                'anio.required'                                 => 'El campo año es requerido.',
                'anio.integer'                                  => 'El campo año debe ser un número entero.',
                'anio.min'                                      => 'El año debe ser igual o mayor a 1900.',
                'anio.max'                                      => 'El año no puede ser mayor a 2100.',
                'mes.required'                                  => 'El campo mes es requerido.',
                'mes.max'                                       => 'El campo mes no puede exceder 30 caracteres.',
                'entidad_federativa.required'                   => 'La entidad federativa es requerida.',
                'entidad_federativa.max'                        => 'La entidad federativa no puede exceder 100 caracteres.',
                'unidades_vehiculares_electricas.required'      => 'Las unidades vehiculares eléctricas son requeridas.',
                'unidades_vehiculares_electricas.integer'       => 'Las unidades vehiculares eléctricas deben ser un número entero.',
                'unidades_vehiculares_electricas.min'           => 'Las unidades vehiculares eléctricas no pueden ser negativas.',
                'unidades_vehiculares_hibridas_plugin.required' => 'Las unidades vehiculares híbridas plugin son requeridas.',
                'unidades_vehiculares_hibridas_plugin.integer'  => 'Las unidades vehiculares híbridas plugin deben ser un número entero.',
                'unidades_vehiculares_hibridas_plugin.min'      => 'Las unidades vehiculares híbridas plugin no pueden ser negativas.',
                'unidades_vehiculares_hibridas.required'        => 'Las unidades vehiculares híbridas son requeridas.',
                'unidades_vehiculares_hibridas.integer'         => 'Las unidades vehiculares híbridas deben ser un número entero.',
                'unidades_vehiculares_hibridas.min'             => 'Las unidades vehiculares híbridas no pueden ser negativas.',
                'numero_mes.required'                           => 'El campo mes_numero es requerido.',
                'numero_mes.integer'                            => 'El campo mes_numero debe ser un número entero.',
                'numero_mes.min'                                => 'El campo mes_numero debe estar entre 1 y 12.',
                'numero_mes.max'                                => 'El campo mes_numero debe estar entre 1 y 12.',
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
