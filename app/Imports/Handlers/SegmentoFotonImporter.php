<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\ExcelValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SegmentoFotonImporter implements ExcelSourceImporter
{
    public function templateHeadings(): array
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

            $rules = [
                'tema'                => 'required',
                'anio'                => 'nullable',
                'mes'                 => 'nullable',
                'marca'               => 'nullable',
                'modelo'              => 'nullable',
                'tipo'                => 'nullable',
                'segmento_inegi'      => 'nullable',
                'segmento_foton'      => 'nullable',
                'origen'              => 'nullable',
                'pais_origen'         => 'nullable',
                'cantidad'            => 'nullable',
                'numero_mes'          => 'nullable',
                'marca_mercado_foton' => 'nullable',
                'unidad_foton'        => 'nullable',
            ];

            $attributes = [
                'anio'                  => 'año',
                'numero_mes'            => 'mes número',
                'segmento_inegi'        => 'segmento INEGI',
                'segmento_foton'        => 'segmento Foton',
                'pais_origen'           => 'país de origen',
                'cantidad'              => 'cantidad',
                'marca_mercado_foton'   => 'marca mercado Foton',
                'unidad_foton'          => 'unidad Foton',
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
