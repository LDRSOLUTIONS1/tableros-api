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
            'prod_est',
            'cobertura',
            'ano',
            'mes_numero',
            'mes',
            'marca',
            'modelo',
            'tipo',
            'segmento',
            'exportacion',
            'pais_destino',
        ];
    }

    public function import(Collection $rows, int $versionId): int
    {
        $records = [];
        $now = now()->toDateTimeString();

        foreach ($rows as $index => $row) {
            $row = collect($row)->toArray();

            // Ignorar filas completamente vacías.
            $hasData = collect($row)->contains(
                fn($value) => $value !== null
                    && trim((string) $value) !== ''
            );

            if (!$hasData) {
                continue;
            }

            // Preparar los datos del Excel.
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
                'exportacion' => $this->integerOrNull(
                    $row['exportacion'] ?? null
                ),
                'pais_destino' => $this->textOrNull(
                    $row['pais_destino'] ?? null
                ),
            ];

            // Validar cada fila.
            $validator = Validator::make($data, [
                'prod_est'      => 'nullable|string|max:255',
                'cobertura'     => 'nullable|string|max:100',
                'anio'          => 'required|integer|min:1900|max:2100',
                'mes_numero'    => 'nullable|integer|between:1,12',
                'mes'           => 'required|string|max:30',
                'marca'         => 'nullable|string|max:100',
                'modelo'        => 'nullable|string|max:150',
                'tipo'          => 'nullable|string|max:100',
                'segmento'      => 'nullable|string|max:100',
                'exportacion'   => 'nullable|integer|min:0',
                'pais_destino'  => 'nullable|string|max:100',
            ], [
                'prod_est.max'          => 'El prod_est no puede tener más de 255 caracteres.',
                'cobertura.max'         => 'La cobertura no puede tener más de 100 caracteres.',
                'anio.required'         => 'El año es obligatorio.',
                'anio.integer'          => 'El año debe ser un número entero.',
                'anio.min'              => 'El año debe ser mayor o igual a 1900.',
                'anio.max'              => 'El año debe ser menor o igual a 2100.',
                'mes_numero.integer'    => 'El número de mes debe ser un número entero.',
                'mes_numero.between'    => 'El número de mes debe estar entre 1 y 12.',
                'mes.required'          => 'El mes es obligatorio.',
                'mes.max'               => 'El mes no puede tener más de 30 caracteres.',
                'marca.max'             => 'La marca no puede tener más de 100 caracteres.',
                'modelo.max'            => 'El modelo no puede tener más de 150 caracteres.',
                'tipo.max'              => 'El tipo no puede tener más de 100 caracteres.',
                'segmento.max'          => 'El segmento no puede tener más de 100 caracteres.',
                'exportacion.integer'   => 'La exportación debe ser un número entero.',
                'exportacion.min'       => 'La exportación no puede ser negativa.',
                'pais_destino.max'      => 'El país de destino no puede tener más de 100 caracteres.',
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

            // La columna exportacion es NOT NULL y tiene default 0.
            $data['exportacion'] = (int) ($data['exportacion'] ?? 0);

            /*
             * Definir qué campos identifican un registro único.
             *
             * exportacion y mes se excluyen porque el primero
             * puede cambiar y el segundo se deriva de mes_numero.
             */
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
                'pais_destino' => $this->normalizeIdentityText(
                    $data['pais_destino'] ?? null
                ),
            ];

            // Generar el identificador de forma automática.
            $registroHash = hash(
                'sha256',
                json_encode(
                    $identity,
                    JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                        | JSON_THROW_ON_ERROR
                )
            );

            // Preparar el registro que se guardará en MySQL.
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

        /*
         * INSERTAR O ACTUALIZAR:
         *
         * Si registro_hash no existe, inserta la fila.
         * Si ya existe, actualiza los datos de esa fila.
         * El índice UNIQUE evita duplicados por esa clave.
         */
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

        // Número de filas válidas procesadas, no de filas nuevas.
        return count($records);
    }

    /**
     * Normalizar textos para que diferencias de mayúsculas
     * o espacios no generen identificadores distintos.
     */
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
