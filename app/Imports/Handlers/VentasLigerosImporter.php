<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VentasLigerosImporter implements ExcelSourceImporter
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

            $validator = Validator::make(
                $data,
                [
                    'tema'                  => 'required|string|max:255',
                    'anio'                  => 'required|integer|min:1900|max:2100',
                    'mes'                   => 'required|string|max:30',
                    'mes_numero'            => 'required|integer|min:1|max:12',
                    'marca'                 => 'required|string|max:100',
                    'modelo'                => 'required|string|max:150',
                    'tipo'                  => 'required|string|max:100',
                    'segmento_inegi'        => 'required|string|max:100',
                    'origen'                => 'required|string|max:50',
                    'pais_origen'           => 'required|string|max:100',
                    'cantidad'              => 'required|integer|min:0',
                    'mercado_inegi'         => 'required|string|max:100',
                    'marca_mercado_foton'   => 'required|string|max:100',
                    'unidad_foton_inegi'    => 'nullable|string|max:150',
                    'seg_pv'                => 'nullable|string|max:100',
                    'origen_2'              => 'nullable|string|max:100',
                ],
                [
                    'tema.required'                 => 'El campo tema es requerido.',
                    'anio.required'                 => 'El campo año es requerido.',
                    'anio.integer'                  => 'El año debe ser un número entero.',
                    'mes.required'                  => 'El campo mes es requerido.',
                    'mes_numero.required'           => 'El número de mes es requerido.',
                    'mes_numero.integer'            => 'El número de mes debe ser un entero.',
                    'mes_numero.min'                => 'El número de mes debe estar entre 1 y 12.',
                    'mes_numero.max'                => 'El número de mes debe estar entre 1 y 12.',
                    'marca.required'                => 'El campo marca es requerido.',
                    'modelo.required'               => 'El campo modelo es requerido.',
                    'tipo.required'                 => 'El campo tipo es requerido.',
                    'segmento_inegi.required'       => 'El campo SegmentoInegi es requerido.',
                    'origen.required'               => 'El campo origen es requerido.',
                    'pais_origen.required'          => 'El campo País_origen es requerido.',
                    'cantidad.required'             => 'El campo cantidad es requerido.',
                    'cantidad.integer'              => 'La cantidad debe ser un número entero.',
                    'cantidad.min'                  => 'La cantidad no puede ser negativa.',
                    'mercado_inegi.required'        => 'El campo MercadoInegi es requerido.',
                    'marca_mercado_foton.required'  => 'El campo MarcaMercadoFoton es requerido.',
                ]
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
