<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\ExcelValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PreciosOfertasImporter implements ExcelSourceImporter
{
    public function templateHeadings(): array
    {
        return [
            'company',
            'model',
            'version',
            'foton',
            'precio',
            'bono',
            'meses_sin_intereses',
            'cargo_por_apertura',
            'tasas_de_interes',
        ];
    }

    public function requiredHeadings(): array
    {
        return [
            'company',
            'model',
            'version',
            'foton',
            'precio',
            'bono',
            'meses_sin_intereses',
            'cargo_por_apertura',
            'tasas_de_interes',
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
                'company' => $this->textOrNull(
                    $row['company'] ?? null
                ),
                'model' => $this->textOrNull(
                    $row['model'] ?? null
                ),
                'version' => $this->textOrNull(
                    $row['version'] ?? null
                ),
                'foton' => $this->textOrNull(
                    $row['foton'] ?? null
                ),
                'precio' => $this->integerOrNull(
                    $row['precio'] ?? null
                ),
                'bono' => $this->integerOrNull(
                    $row['bono'] ?? null
                ),
                'meses_sin_intereses' => $this->integerOrNull(
                    $row['meses_sin_intereses'] ?? null
                ),
                'cargo_por_apertura' => $this->decimalOrNull(
                    $row['cargo_por_apertura'] ?? null
                ),
                'tasas_de_interes' => $this->decimalOrNull(
                    $row['tasas_de_interes'] ?? null
                ),
            ];

            $rules = [
                'company'             => 'required|string|max:150',
                'model'               => 'required|string|max:150',
                'version'             => 'required|string|max:150',
                'foton'               => 'required|string|max:150',
                'precio'              => 'required|integer|min:0',
                'bono'                => 'required|integer|min:0',
                'meses_sin_intereses' => 'nullable|integer|min:0|max:255',
                'cargo_por_apertura'  => 'nullable|numeric|min:0',
                'tasas_de_interes'    => 'nullable|numeric|min:0|max:100',
            ];

            $attributes = [
                'company'               => 'Company',
                'model'                 => 'Model',
                'version'               => 'Version',
                'foton'                 => 'Foton',
                'precio'                => 'precio',
                'bono'                  => 'bono',
                'meses_sin_intereses'   => 'meses sin intereses',
                'cargo_por_apertura'    => 'cargo por apertura',
                'tasas_de_interes'      => 'tasa de interés',
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
                'company' => $this->normalizeIdentityText(
                    $data['company']
                ),
                'model' => $this->normalizeIdentityText(
                    $data['model']
                ),
                'version' => $this->normalizeIdentityText(
                    $data['version']
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
            'company',
            'model',
            'version',
            'foton',
            'precio',
            'bono',
            'meses_sin_intereses',
            'cargo_por_apertura',
            'tasas_de_interes',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('precios_ofertas')->upsert(
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

    private function decimalOrNull(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
