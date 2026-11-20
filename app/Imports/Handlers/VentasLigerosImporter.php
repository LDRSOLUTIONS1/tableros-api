<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\ExcelValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VentasLigerosImporter implements ExcelSourceImporter
{
    public function templateHeadings(): array
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
            'origen_2',
        ];
    }

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
            'origen_2',
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
                'mes_numero' => $this->integerOrNull(
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
                'seg_pv' => $this->textOrNull(
                    $row['seg_p_v'] ?? null
                ),
                'origen_2' => $this->textOrNull(
                    $row['origen_2'] ?? null
                ),
            ];

            $rules = [
                'tema'                => 'required',
                'anio'                => 'nullable',
                'mes'                 => 'nullable',
                'mes_numero'          => 'nullable',
                'marca'               => 'nullable',
                'modelo'              => 'nullable',
                'tipo'                => 'nullable',
                'segmento_inegi'      => 'nullable',
                'origen'              => 'nullable',
                'pais_origen'         => 'nullable',
                'cantidad'            => 'nullable',
                'mercado_inegi'       => 'nullable',
                'marca_mercado_foton' => 'nullable',
                'unidad_foton_inegi'  => 'nullable',
                'seg_pv'              => 'nullable',
                'origen_2'            => 'nullable',
            ];

            $attributes = [
                'anio'                  => 'año',
                'mes_numero'            => 'mes número',
                'segmento_inegi'        => 'segmento INEGI',
                'pais_origen'           => 'país de origen',
                'cantidad'              => 'cantidad',
                'mercado_inegi'         => 'mercado INEGI',
                'marca_mercado_foton'   => 'marca mercado Foton',
                'unidad_foton_inegi'    => 'unidad Foton INEGI',
                'seg_pv'                => 'segmento P y V',
                'origen_2'              => 'origen 2',
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
                            . implode(
                                ' ',
                                $validator->errors()->all()
                            ),
                    ],
                ]);
            }

            $data = $validator->validated();

            $identity = [
                'tema' => $this->normalizeIdentityText(
                    $data['tema']
                ),
                'anio' => (int) $data['anio'],
                'mes_numero' => (int) $data['mes_numero'],
                'marca' => $this->normalizeIdentityText(
                    $data['marca']
                ),
                'modelo' => $this->normalizeIdentityText(
                    $data['modelo']
                ),
                'tipo' => $this->normalizeIdentityText(
                    $data['tipo']
                ),
                'segmento_inegi' => $this->normalizeIdentityText(
                    $data['segmento_inegi']
                ),
                'origen' => $this->normalizeIdentityText(
                    $data['origen']
                ),
                'pais_origen' => $this->normalizeIdentityText(
                    $data['pais_origen']
                ),
                'mercado_inegi' => $this->normalizeIdentityText(
                    $data['mercado_inegi']
                ),
                'marca_mercado_foton' => $this->normalizeIdentityText(
                    $data['marca_mercado_foton']
                ),
                'unidad_foton_inegi' => $this->normalizeIdentityText(
                    $data['unidad_foton_inegi'] ?? null
                ),
                'seg_pv' => $this->normalizeIdentityText(
                    $data['seg_pv'] ?? null
                ),
                'origen_2' => $this->normalizeIdentityText(
                    $data['origen_2'] ?? null
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
            'mes_numero',
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
            'seg_pv',
            'origen_2',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('ventas_publico')->upsert(
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
