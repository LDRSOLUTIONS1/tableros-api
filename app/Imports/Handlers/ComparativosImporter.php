<?php

namespace App\Imports\Handlers;

use App\Contracts\ExcelSourceImporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ComparativosImporter implements ExcelSourceImporter
{
    public function requiredHeadings(): array
    {
        return [
            'id',
            'company',
            'model',
            'version',
            'year',
            'price',
            'bonus',
            'price_with_bonus',
            'sales_model_ytd_2025',
            'sales_version_ytd_2025',
            'market_share_by_segment',
            'foton',
            'origin',
            'avaliable',
            'segment',
            'sub_segment',
            'bodywork',
            'drive',
            'energy_system',
            'engine_baterry',
            'displacement',
            'battery_capacity_kwh',
            'transmission',
            'turbo',
            'autonomy',
            'aceleration_time',
            'maximum_speed',
            'hp',
            'lb_ft',
            'long',
            'tank',
            'payload_capacity',
            'towing_capacity',
            'seats',
            'vol_m3',
            'class',
            'gwv',
            'emission_standard',
            'range',
            'range_price',
            'charging_time_hours',
            'towing_capacity_price',
            'price_per_hp',
            'price_per_lb_pie',
            'payload_per_dollar',
            'n_airbags',
            'n_adas',
            'engine_brand',
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
                'codigo' => $this->textOrNull(
                    $row['id'] ?? null
                ),
                'company' => $this->textOrNull(
                    $row['company'] ?? null
                ),
                'model' => $this->textOrNull(
                    $row['model'] ?? null
                ),
                'version' => $this->textOrNull(
                    $row['version'] ?? null
                ),
                'year' => $this->integerOrNull(
                    $row['year'] ?? null
                ),
                'price' => $this->decimalOrNull(
                    $row['price'] ?? null
                ),
                'bonus' => $this->decimalOrNull(
                    $row['bonus'] ?? null
                ),
                'price_with_bonus' => $this->decimalOrNull(
                    $row['price_with_bonus'] ?? null
                ),
                'sales_model_ytd_2025' => $this->integerOrNull(
                    $row['sales_model_ytd_2025'] ?? null
                ),
                'sales_version_ytd_2025' => $this->integerOrNull(
                    $row['sales_version_ytd_2025'] ?? null
                ),
                'market_share_by_segment' => $this->decimalOrNull(
                    $row['market_share_by_segment'] ?? null
                ),
                'foton' => $this->textOrNull(
                    $row['foton'] ?? null
                ),
                'origin' => $this->textOrNull(
                    $row['origin'] ?? null
                ),
                'avaliable' => $this->textOrNull(
                    $row['avaliable'] ?? null
                ),
                'segment' => $this->textOrNull(
                    $row['segment'] ?? null
                ),
                'sub_segment' => $this->textOrNull(
                    $row['sub_segment'] ?? null
                ),
                'bodywork' => $this->textOrNull(
                    $row['bodywork'] ?? null
                ),
                'drive' => $this->textOrNull(
                    $row['drive'] ?? null
                ),
                'energy_system' => $this->textOrNull(
                    $row['energy_system'] ?? null
                ),
                'engine_baterry' => $this->textOrNull(
                    $row['engine_baterry'] ?? null
                ),
                'displacement' => $this->integerOrNull(
                    $row['displacement'] ?? null
                ),
                'battery_capacity_kw_h' => $this->decimalOrNull(
                    $row['battery_capacity_kwh'] ?? null
                ),
                'transmission' => $this->textOrNull(
                    $row['transmission'] ?? null
                ),
                'turbo' => $this->textOrNull(
                    $row['turbo'] ?? null
                ),
                'autonomy' => $this->decimalOrNull(
                    $row['autonomy'] ?? null
                ),
                'aceleration_time' => $this->decimalOrNull(
                    $row['aceleration_time'] ?? null
                ),
                'maximum_speed' => $this->integerOrNull(
                    $row['maximum_speed'] ?? null
                ),
                'hp' => $this->integerOrNull(
                    $row['hp'] ?? null
                ),
                'lb_ft' => $this->decimalOrNull(
                    $row['lb_ft'] ?? null
                ),
                'long' => $this->integerOrNull(
                    $row['long'] ?? null
                ),
                'tank' => $this->decimalOrNull(
                    $row['tank'] ?? null
                ),
                'payload_capacity' => $this->decimalOrNull(
                    $row['payload_capacity'] ?? null
                ),
                'towing_capacity' => $this->decimalOrNull(
                    $row['towing_capacity'] ?? null
                ),
                'seats' => $this->integerOrNull(
                    $row['seats'] ?? null
                ),
                'vol_m3' => $this->decimalOrNull(
                    $row['vol_m3'] ?? null
                ),
                'class' => $this->textOrNull(
                    $row['class'] ?? null
                ),
                'gwv' => $this->integerOrNull(
                    $row['gwv'] ?? null
                ),
                'emission_standard' => $this->textOrNull(
                    $row['emission_standard'] ?? null
                ),
                'range' => $this->decimalOrNull(
                    $row['range'] ?? null
                ),
                'range_precio' => $this->decimalOrNull(
                    $row['range_price'] ?? null
                ),
                'charging_time_hrs' => $this->decimalOrNull(
                    $row['charging_time_hours'] ?? null
                ),
                'towing_capacity_precio' => $this->decimalOrNull(
                    $row['towing_capacity_price'] ?? null
                ),
                'precio_hp' => $this->decimalOrNull(
                    $row['price_per_hp'] ?? null
                ),
                'precio_lb_pie' => $this->decimalOrNull(
                    $row['price_per_lb_pie'] ?? null
                ),
                'payload_precio' => $this->decimalOrNull(
                    $row['payload_per_dollar'] ?? null
                ),
                'n_airbags' => $this->integerOrNull(
                    $row['n_airbags'] ?? null
                ),
                'n_adas' => $this->integerOrNull(
                    $row['n_adas'] ?? null
                ),
                'engine_brand' => $this->textOrNull(
                    $row['engine_brand'] ?? null
                ),
            ];

            $validator = Validator::make($data, [
                'codigo'                            => 'required|string|max:50',
                'company'                          => 'required|string|max:100',
                'model'                            => 'required|string|max:150',
                'version'                           => 'required|string|max:150',
                'year'                              => 'nullable|integer|min:1900|max:2100',
                'price'                            => 'required|numeric|min:0',
                'bonus'                              => 'required|numeric|min:0',
                'price_with_bonus'                   => 'required|numeric|min:0',
                'sales_model_ytd_2025'            => 'nullable|integer|min:0',
                'sales_version_ytd_2025'           => 'nullable|integer|min:0',
                'market_share_by_segment'    => 'nullable|numeric|min:0|max:100',
                'foton'                             => 'nullable|string|max:100',
                'origin'                            => 'nullable|string|max:100',
                'avaliable'                        => 'nullable|string|max:50',
                'segment'                          => 'nullable|string|max:100',
                'sub_segment'                      => 'nullable|string|max:100',
                'bodywork'                        => 'nullable|string|max:100',
                'drive'                          => 'nullable|string|max:50',
                'energy_system'                   => 'nullable|string|max:50',
                'engine_baterry'                     => 'nullable|string|max:255',
                'displacement'                        => 'nullable|integer|min:0',
                'battery_capacity_kw_h'             => 'nullable|numeric|min:0',
                'transmission'                       => 'nullable|string|max:100',
                'turbo'                             => 'nullable|string|max:50',
                'autonomy'                         => 'nullable|numeric|min:0',
                'aceleration_time'                => 'nullable|numeric|min:0',
                'maximum_speed'                  => 'nullable|integer|min:0',
                'hp'                                => 'nullable|integer|min:0',
                'lb_ft'                             => 'nullable|numeric|min:0',
                'long'                          => 'nullable|integer|min:0',
                'tank'                            => 'nullable|numeric|min:0',
                'payload_capacity'                   => 'nullable|numeric|min:0',
                'towing_capacity'                => 'nullable|numeric|min:0',
                'seats'                          => 'nullable|integer|min:0',
                'vol_m3'                        => 'nullable|numeric|min:0',
                'class'                             => 'nullable|string|max:100',
                'gwv'                               => 'nullable|integer|min:0',
                'emission_standard'                   => 'nullable|string|max:100',
                'range'                             => 'nullable|numeric|min:0',
                'range_precio'                      => 'nullable|numeric|min:0',
                'charging_time_hrs'                => 'nullable|numeric|min:0',
                'towing_capacity_precio'         => 'nullable|numeric|min:0',
                'precio_hp'                     => 'nullable|numeric|min:0',
                'precio_lb_pie'                 => 'nullable|numeric|min:0',
                'payload_precio'        => 'nullable|numeric|min:0',
                'n_airbags'                    => 'nullable|integer|min:0',
                'n_adas'                       => 'nullable|integer|min:0',
                'engine_brand'                       => 'nullable|string|max:150',
            ], [
                'required' => 'El campo :attribute es requerido.',
                'string'   => 'El campo :attribute debe ser texto.',
                'integer'  => 'El campo :attribute debe ser un número entero.',
                'numeric'  => 'El campo :attribute debe ser numérico.',
                'min'      => 'El campo :attribute no cumple con el mínimo permitido.',
                'max'      => 'El campo :attribute excede el límite permitido.',
            ], [
                'codigo'          => 'id',
                'company'        => 'Company',
                'model'          => 'Model',
                'version'         => 'Version',
                'year'            => 'Year',
                'price'          => 'Price',
                'bonus'            => 'Bonus',
                'price_with_bonus' => 'Price_With_Bonus',
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

            $registroHash = hash(
                'sha256',
                json_encode(
                    [
                        'codigo' => $this->normalizeIdentityText(
                            $data['codigo']
                        ),
                    ],
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
            'codigo',
            'company',
            'model',
            'version',
            'year',
            'price',
            'bonus',
            'price_with_bonus',
            'sales_model_ytd_2025',
            'sales_version_ytd_2025',
            'market_share_by_segment',
            'foton',
            'origin',
            'avaliable',
            'segment',
            'sub_segment',
            'bodywork',
            'drive',
            'energy_system',
            'engine_baterry',
            'displacement',
            'battery_capacity_kw_h',
            'transmission',
            'turbo',
            'autonomy',
            'aceleration_time',
            'maximum_speed',
            'hp',
            'lb_ft',
            'long',
            'tank',
            'payload_capacity',
            'towing_capacity',
            'seats',
            'vol_m3',
            'class',
            'gwv',
            'emission_standard',
            'range',
            'range_precio',
            'charging_time_hrs',
            'towing_capacity_precio',
            'precio_hp',
            'precio_lb_pie',
            'payload_precio',
            'n_airbags',
            'n_adas',
            'engine_brand',
            'excel_import_version_id',
            'updated_at',
        ];

        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('comparativos')->upsert(
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
        if ($value === null) {
            return null;
        }

        $value = trim(str_replace(
            ["\u{00A0}", ' '],
            '',
            (string) $value
        ));

        if ($value === '') {
            return null;
        }

        if (
            str_contains($value, ',')
            && str_contains($value, '.')
        ) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (str_contains($value, ',')) {
            $value = preg_match('/,\d{3}$/', $value)
                ? str_replace(',', '', $value)
                : str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
