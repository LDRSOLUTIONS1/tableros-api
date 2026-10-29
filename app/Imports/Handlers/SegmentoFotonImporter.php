<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SegmentoFotonImporter implements ExcelSourceImporter
{
    public function requiredHeadings(): array
    {
        return [
            'tema',
            'anio',
            'mes',
            'marca',
            'modelo',
            'tipo',
            'segmento_inegi',
            'segmento_foton_1',
            'origen',
            'pais_origen',
            'cantidad',
            'mes_numero',
            'marca_mercado_foton',
            'unidad_foton',
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
                    $row['anio'] ?? null
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
                'segmento_inegi' => $this->textOrNull(
                    $row['segmento_inegi'] ?? null
                ),
                'segmento_foton' => $this->textOrNull(
                    $row['segmento_foton_1'] ?? null
                ),
                'origen' => $this->textOrNull(
                    $row['origen'] ?? null
                ),
                'pais_origen' => $this->textOrNull(
                    $row['pais_origen'] ?? null
                ),
                'cantidad' => $this->integerOrNull(
                    $row['cantidad'] ?? null
                ),
                'numero_mes' => $this->integerOrNull(
                    $row['mes_numero'] ?? null
                ),
                'marca_mercado_foton' => $this->textOrNull(
                    $row['marca_mercado_foton'] ?? null
                ),
                'unidad_foton' => $this->textOrNull(
                    $row['unidad_foton'] ?? null
                ),
            ];

            $validator = Validator::make($data, [
                'tema'                => 'required|string|max:255',
                'anio'                => 'required|integer|min:1900|max:2100',
                'mes'                 => 'required|string|max:30',
                'marca'               => 'required|string|max:100',
                'modelo'              => 'required|string|max:150',
                'tipo'                => 'required|string|max:100',
                'segmento_inegi'      => 'required|string|max:100',
                'segmento_foton'      => 'required|string|max:100',
                'origen'              => 'required|string|max:100',
                'pais_origen'         => 'required|string|max:100',
                'cantidad'            => 'required|integer|min:0',
                'numero_mes'          => 'required|integer|min:1|max:12',
                'marca_mercado_foton' => 'nullable|string|max:100',
                'unidad_foton'        => 'nullable|string|max:150',
            ], [
                'tema.required'                => 'El campo tema es requerido.',
                'tema.max'                     => 'El campo tema no puede exceder 255 caracteres.',
                'anio.required'                => 'El campo año es requerido.',
                'anio.integer'                 => 'El campo año debe ser un número entero.',
                'anio.min'                     => 'El año debe ser igual o mayor a 1900.',
                'anio.max'                     => 'El año no puede ser mayor a 2100.',
                'mes.required'                 => 'El campo mes es requerido.',
                'mes.max'                      => 'El campo mes no puede exceder 30 caracteres.',
                'marca.required'               => 'El campo marca es requerido.',
                'modelo.required'              => 'El campo modelo es requerido.',
                'tipo.required'                => 'El campo tipo es requerido.',
                'segmento_inegi.required'      => 'El campo segmento_inegi es requerido.',
                'segmento_foton.required'      => 'El campo segmento_foton es requerido.',
                'origen.required'              => 'El campo origen es requerido.',
                'pais_origen.required'         => 'El país de origen es requerido.',
                'cantidad.required'            => 'El campo cantidad es requerido.',
                'cantidad.integer'             => 'La cantidad debe ser un número entero.',
                'cantidad.min'                 => 'La cantidad no puede ser negativa.',
                'numero_mes.required'          => 'El campo mes_numero es requerido.',
                'numero_mes.integer'           => 'El campo mes_numero debe ser un número entero.',
                'numero_mes.min'               => 'El campo mes_numero debe estar entre 1 y 12.',
                'numero_mes.max'               => 'El campo mes_numero debe estar entre 1 y 12.',
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
                'marca' => $this->normalizeIdentityText(
                    $data['marca']
                ),
                'modelo' => $this->normalizeIdentityText(
                    $data['modelo']
                ),
                'tipo' => $this->normalizeIdentityText(
                    $data['tipo']
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
            'marca',
            'modelo',
            'tipo',
            'segmento_inegi',
            'segmento_foton',
            'origen',
            'pais_origen',
            'cantidad',
            'numero_mes',
            'marca_mercado_foton',
            'unidad_foton',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('segmento_foton')->upsert(
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
