<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VentasVehiculosImporter implements ExcelSourceImporter
{
    public function requiredHeadings(): array
    {
        return [
            'tema',
            'ano',
            'mes',
            'mes_numero',
            'marca',
            'modelo',
            'tipo',
            'segmentoinegi',
            'origen',
            'pais_origen',
            'cantidad',
            'mercadoinegi',
            'marcamercadofoton',
            'unidadfotoninegi',
            'seg_p_v',
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
                'numero_mes' => $this->integerOrNull(
                    $row['mes_numero'] ?? null
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
                    $row['segmentoinegi'] ?? null
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
                'mercado_inegi' => $this->textOrNull(
                    $row['mercadoinegi'] ?? null
                ),
                'marca_mercado_foton' => $this->textOrNull(
                    $row['marcamercadofoton'] ?? null
                ),
                'unidad_foton_inegi' => $this->textOrNull(
                    $row['unidadfotoninegi'] ?? null
                ),
                'seg_p_v' => $this->textOrNull(
                    $row['seg_p_v'] ?? null
                ),
            ];

            $validator = Validator::make($data, [
                'tema'                  => 'required|string|max:255',
                'anio'                  => 'required|integer|min:1900|max:2100',
                'mes'                   => 'required|string|max:30',
                'numero_mes'            => 'required|integer|min:1|max:12',
                'marca'                 => 'required|string|max:100',
                'modelo'                => 'required|string|max:150',
                'tipo'                  => 'required|string|max:100',
                'segmento_inegi'        => 'required|string|max:100',
                'origen'                => 'required|string|max:100',
                'pais_origen'           => 'required|string|max:100',
                'cantidad'              => 'required|integer|min:0',
                'mercado_inegi'         => 'required|string|max:100',
                'marca_mercado_foton'   => 'nullable|string|max:100',
                'unidad_foton_inegi'    => 'nullable|string|max:150',
                'seg_p_v'               => 'nullable|string|max:100',
            ], [
                'required' => 'El campo :attribute es requerido.',
                'integer'  => 'El campo :attribute debe ser un número entero.',
                'min'      => 'El campo :attribute no cumple con el mínimo permitido.',
                'max'      => 'El campo :attribute excede el límite permitido.',
                'string'   => 'El campo :attribute debe ser texto.',
            ], [
                'tema'                => 'tema',
                'anio'                => 'año',
                'mes'                 => 'mes',
                'numero_mes'          => 'mes_numero',
                'marca'               => 'marca',
                'modelo'              => 'modelo',
                'tipo'                => 'tipo',
                'segmento_inegi'      => 'segmento INEGI',
                'origen'              => 'origen',
                'pais_origen'         => 'país de origen',
                'cantidad'            => 'cantidad',
                'mercado_inegi'       => 'mercado INEGI',
                'marca_mercado_foton' => 'marca mercado Foton',
                'unidad_foton_inegi'  => 'unidad Foton INEGI',
                'seg_p_v'             => 'segmento P y V',
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
            'numero_mes',
            'marca',
            'modelo',
            'tipo',
            'segmento_inegi',
            'origen',
            'pais_origen',
            'cantidad',
            'mercado_inegi',
            'marca_mercado_foton',
            'unidad_foton_inegi',
            'seg_p_v',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('ventas_vehiculos')->upsert(
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
