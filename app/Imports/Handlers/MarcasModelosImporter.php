<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MarcasModelosImporter implements ExcelSourceImporter
{
    public function requiredHeadings(): array
    {
        return [
            'tema',
            'ano',
            'mes',
            'marca_im',
            'marca_inegi',
            'mod_im',
            'mod_inegi',
            'version_im',
            'segmento_e',
            'segmento_b',
            'segmento_c',
            'segmento_inegi',
            'tipo',
            'pais_marca',
            'origen',
            'pais_origen',
            'energia',
            'ventas',
            'columna1',
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
                'marca_im' => $this->textOrNull(
                    $row['marca_im'] ?? null
                ),
                'marca_inegi' => $this->textOrNull(
                    $row['marca_inegi'] ?? null
                ),
                'mod_im' => $this->textOrNull(
                    $row['mod_im'] ?? null
                ),
                'mod_inegi' => $this->textOrNull(
                    $row['mod_inegi'] ?? null
                ),
                'version_im' => $this->textOrNull(
                    $row['version_im'] ?? null
                ),
                'segmento_e' => $this->textOrNull(
                    $row['segmento_e'] ?? null
                ),
                'segmento_b' => $this->textOrNull(
                    $row['segmento_b'] ?? null
                ),
                'segmento_c' => $this->textOrNull(
                    $row['segmento_c'] ?? null
                ),
                'segmento_inegi' => $this->textOrNull(
                    $row['segmento_inegi'] ?? null
                ),
                'tipo' => $this->textOrNull(
                    $row['tipo'] ?? null
                ),
                'pais_marca' => $this->textOrNull(
                    $row['pais_marca'] ?? null
                ),
                'origen' => $this->textOrNull(
                    $row['origen'] ?? null
                ),
                'pais_origen' => $this->textOrNull(
                    $row['pais_origen'] ?? null
                ),
                'energia' => $this->textOrNull(
                    $row['energia'] ?? null
                ),
                'ventas' => $this->integerOrNull(
                    $row['ventas'] ?? null
                ),
                'columna1' => $this->textOrNull(
                    $row['columna1'] ?? null
                ),
                'numero_mes' => $this->integerOrNull(
                    $row['mes_numero'] ?? null
                ),
            ];

            $validator = Validator::make($data, [
                'tema'              => 'required|string|max:255',
                'anio'              => 'required|integer|min:1900|max:2100',
                'mes'               => 'required|string|max:30',
                'marca_im'          => 'required|string|max:100',
                'marca_inegi'       => 'required|string|max:100',
                'mod_im'            => 'required|string|max:150',
                'mod_inegi'         => 'required|string|max:150',
                'version_im'        => 'required|string|max:150',
                'segmento_e'        => 'required|string|max:100',
                'segmento_b'        => 'required|string|max:100',
                'segmento_c'        => 'required|string|max:100',
                'segmento_inegi'    => 'required|string|max:100',
                'tipo'              => 'required|string|max:100',
                'pais_marca'        => 'required|string|max:100',
                'origen'            => 'required|string|max:100',
                'pais_origen'       => 'required|string|max:100',
                'energia'           => 'required|string|max:50',
                'ventas'            => 'required|integer|min:0',
                'columna1'          => 'required|string|max:100',
                'numero_mes'        => 'required|integer|min:1|max:12',
            ], [
                'tema.required'             => 'El campo tema es requerido.',
                'tema.max'                  => 'El campo tema no puede exceder 255 caracteres.',
                'anio.required'             => 'El campo año es requerido.',
                'anio.integer'              => 'El campo año debe ser un número entero.',
                'anio.min'                  => 'El año debe ser igual o mayor a 1900.',
                'anio.max'                  => 'El año no puede ser mayor a 2100.',
                'mes.required'              => 'El campo mes es requerido.',
                'mes.max'                   => 'El campo mes no puede exceder 30 caracteres.',
                'marca_im.required'         => 'El campo marca_im es requerido.',
                'marca_inegi.required'      => 'El campo marca_inegi es requerido.',
                'mod_im.required'           => 'El campo mod_im es requerido.',
                'mod_inegi.required'        => 'El campo mod_inegi es requerido.',
                'version_im.required'       => 'El campo version_im es requerido.',
                'segmento_e.required'       => 'El campo segmento_e es requerido.',
                'segmento_b.required'       => 'El campo segmento_b es requerido.',
                'segmento_c.required'       => 'El campo segmento_c es requerido.',
                'segmento_inegi.required'   => 'El campo segmento_inegi es requerido.',
                'tipo.required'             => 'El campo tipo es requerido.',
                'pais_marca.required'       => 'El país de la marca es requerido.',
                'origen.required'           => 'El campo origen es requerido.',
                'pais_origen.required'      => 'El país de origen es requerido.',
                'energia.required'          => 'El campo energía es requerido.',
                'ventas.required'           => 'El campo ventas es requerido.',
                'ventas.integer'            => 'Las ventas deben ser un número entero.',
                'ventas.min'                => 'Las ventas no pueden ser negativas.',
                'columna1.required'         => 'El campo columna1 es requerido.',
                'numero_mes.required'       => 'El campo mes_numero es requerido.',
                'numero_mes.integer'        => 'El campo mes_numero debe ser un número entero.',
                'numero_mes.min'            => 'El campo mes_numero debe estar entre 1 y 12.',
                'numero_mes.max'            => 'El campo mes_numero debe estar entre 1 y 12.',
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
                'columna1' => $this->normalizeIdentityText(
                    $data['columna1']
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
            'marca_im',
            'marca_inegi',
            'mod_im',
            'mod_inegi',
            'version_im',
            'segmento_e',
            'segmento_b',
            'segmento_c',
            'segmento_inegi',
            'tipo',
            'pais_marca',
            'origen',
            'pais_origen',
            'energia',
            'ventas',
            'columna1',
            'numero_mes',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('marcas_modelos')->upsert(
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
