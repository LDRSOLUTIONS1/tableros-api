<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\ExcelValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MarcasModelosImporter implements ExcelSourceImporter
{
    public function templateHeadings(): array
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

            $rules = [
                'tema'           => 'required',
                'anio'           => 'nullable',
                'mes'            => 'nullable',
                'marca_im'       => 'nullable',
                'marca_inegi'    => 'nullable',
                'mod_im'         => 'nullable',
                'mod_inegi'      => 'nullable',
                'version_im'     => 'nullable',
                'segmento_e'     => 'nullable',
                'segmento_b'     => 'nullable',
                'segmento_c'     => 'nullable',
                'segmento_inegi' => 'nullable',
                'tipo'           => 'nullable',
                'pais_marca'     => 'nullable',
                'origen'         => 'nullable',
                'pais_origen'    => 'nullable',
                'energia'        => 'nullable',
                'ventas'         => 'nullable',
                'columna1'       => 'nullable',
                'numero_mes'     => 'nullable',
            ];

            $attributes = [
                'anio'              => 'año',
                'numero_mes'        => 'mes número',
                'marca_im'          => 'marca IM',
                'marca_inegi'       => 'marca INEGI',
                'mod_im'            => 'modelo IM',
                'mod_inegi'         => 'modelo INEGI',
                'version_im'        => 'versión IM',
                'segmento_e'        => 'segmento E',
                'segmento_b'        => 'segmento B',
                'segmento_c'        => 'segmento C',
                'segmento_inegi'    => 'segmento INEGI',
                'pais_marca'        => 'país de la marca',
                'pais_origen'       => 'país de origen',
                'energia'           => 'energía',
                'ventas'            => 'ventas',
                'columna1'          => 'columna 1',
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
