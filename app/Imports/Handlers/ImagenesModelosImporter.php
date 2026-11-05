<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Support\ExcelValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ImagenesModelosImporter implements ExcelSourceImporter
{
    public function templateHeadings(): array
    {
        return [
            'company',
            'model',
            'version',
            'foton',
            'imageurl',
            'imagelogo',
        ];
    }

    public function requiredHeadings(): array
    {
        return [
            'company',
            'model',
            'version',
            'foton',
            'imageurl',
            'imagelogo',
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
                'image_url' => $this->textOrNull(
                    $row['imageurl'] ?? null
                ),
                'image_logo' => $this->textOrNull(
                    $row['imagelogo'] ?? null
                ),
            ];

            $rules = [
                'company'    => 'required|string|max:150',
                'model'      => 'required|string|max:150',
                'version'    => 'required|string|max:150',
                'foton'      => 'required|string|max:150',
                'image_url'  => 'nullable|url|max:2048',
                'image_logo' => 'nullable|url|max:2048',
            ];

            $attributes = [
                'company'       => 'Company',
                'model'         => 'Model',
                'version'       => 'Version',
                'foton'         => 'Foton',
                'image_url'     => 'Image URL',
                'image_logo'    => 'Image Logo',
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
            'image_url',
            'image_logo',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('imagenes_modelos')->upsert(
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
}
