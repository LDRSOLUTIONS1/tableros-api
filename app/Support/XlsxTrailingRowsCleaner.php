<?php

namespace App\Support;

use RuntimeException;
use Throwable;
use ZipArchive;

class XlsxTrailingRowsCleaner
{
    public function cleanCopy(string $sourcePath): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'xlsx_');

        if ($temp === false) {
            throw new RuntimeException(
                'No fue posible crear el archivo temporal.'
            );
        }

        $tempPath = $temp . '.xlsx';

        if (!rename($temp, $tempPath)) {
            @unlink($temp);

            throw new RuntimeException(
                'No fue posible preparar el archivo temporal.'
            );
        }

        try {
            if (!copy($sourcePath, $tempPath)) {
                throw new RuntimeException(
                    'No fue posible copiar el archivo Excel.'
                );
            }

            $zip = new ZipArchive();

            if ($zip->open($tempPath) !== true) {
                throw new RuntimeException(
                    'No fue posible abrir el archivo XLSX.'
                );
            }

            try {
                $sheetPath = $this->firstWorksheetPath($zip);
                $xml = $zip->getFromName($sheetPath);

                if (!is_string($xml)) {
                    throw new RuntimeException(
                        'No fue posible leer la hoja de Excel.'
                    );
                }

                [$lastRow, $lastColumn] = $this->findDataBounds($xml);

                $lastRow = max(1, $lastRow);

                $rowPattern = '/<row\b[^>]*\/>|<row\b[^>]*>.*?<\/row>/s';

                $cleanXml = preg_replace_callback(
                    $rowPattern,
                    function ($match) use ($lastRow) {
                        if (!preg_match(
                            '/\br="(\d+)"/',
                            $match[0],
                            $row
                        )) {
                            return $match[0];
                        }

                        return (int) $row[1] > $lastRow
                            ? ''
                            : $match[0];
                    },
                    $xml
                );

                if (!is_string($cleanXml)) {
                    throw new RuntimeException(
                        'No fue posible limpiar el rango de Excel.'
                    );
                }

                $cleanXml = preg_replace(
                    '/<dimension\b[^>]*\/>/',
                    '<dimension ref="A1:'
                        . $lastColumn
                        . $lastRow
                        . '"/>',
                    $cleanXml,
                    1
                ) ?? $cleanXml;

                if (
                    !$zip->deleteName($sheetPath)
                    || !$zip->addFromString($sheetPath, $cleanXml)
                ) {
                    throw new RuntimeException(
                        'No fue posible actualizar la hoja de Excel.'
                    );
                }
            } finally {
                $zip->close();
            }

            return $tempPath;
        } catch (Throwable $e) {
            @unlink($tempPath);

            throw $e;
        }
    }

    private function findDataBounds(string $xml): array
    {
        $lastRow = 0;
        $maxColumn = 0;

        $rowPattern = '/<row\b[^>]*\/>|<row\b[^>]*>.*?<\/row>/s';

        preg_replace_callback(
            $rowPattern,
            function ($match) use (&$lastRow, &$maxColumn) {
                $rowXml = $match[0];

                if (!preg_match('/\br="(\d+)"/', $rowXml, $row)) {
                    return '';
                }

                preg_match_all(
                    '/<c\b([^>]*)\/>|<c\b([^>]*)>(.*?)<\/c>/s',
                    $rowXml,
                    $cells,
                    PREG_SET_ORDER
                );

                $hasData = false;

                foreach ($cells as $cell) {
                    $attributes = ($cell[1] ?? '') !== ''
                        ? $cell[1]
                        : ($cell[2] ?? '');

                    $content = $cell[3] ?? '';

                    $hasFormula = preg_match(
                        '/<f\b/',
                        $content
                    ) === 1;

                    $hasValue = preg_match(
                        '/<(?:v|t)\b[^>]*>\s*[^<]*\S[^<]*\s*<\/(?:v|t)>/s',
                        $content
                    ) === 1;

                    if (!$hasFormula && !$hasValue) {
                        continue;
                    }

                    $hasData = true;

                    if (preg_match(
                        '/\br="([A-Z]+)\d+"/i',
                        $attributes,
                        $column
                    )) {
                        $maxColumn = max(
                            $maxColumn,
                            $this->columnToNumber($column[1])
                        );
                    }
                }

                if ($hasData) {
                    $lastRow = max($lastRow, (int) $row[1]);
                }

                return '';
            },
            $xml
        );

        return [
            $lastRow,
            $this->numberToColumn($maxColumn),
        ];
    }

    private function firstWorksheetPath(ZipArchive $zip): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationsXml = $zip->getFromName(
            'xl/_rels/workbook.xml.rels'
        );

        if ($workbookXml === false || $relationsXml === false) {
            throw new RuntimeException(
                'La estructura del archivo XLSX no es válida.'
            );
        }

        $workbook = simplexml_load_string(
            $workbookXml,
            \SimpleXMLElement::class,
            LIBXML_NONET
        );

        $relations = simplexml_load_string(
            $relationsXml,
            \SimpleXMLElement::class,
            LIBXML_NONET
        );

        if (!$workbook || !$relations) {
            throw new RuntimeException(
                'No fue posible leer la estructura del libro.'
            );
        }

        $workbook->registerXPathNamespace(
            'main',
            'http://schemas.openxmlformats.org/spreadsheetml/2006/main'
        );

        $sheets = $workbook->xpath(
            '/main:workbook/main:sheets/main:sheet'
        );

        if (!$sheets) {
            throw new RuntimeException(
                'El archivo no contiene hojas de Excel.'
            );
        }

        $attributes = $sheets[0]->attributes(
            'http://schemas.openxmlformats.org/officeDocument/2006/relationships'
        );

        $relationshipId = (string) $attributes['id'];

        $relations->registerXPathNamespace(
            'pkg',
            'http://schemas.openxmlformats.org/package/2006/relationships'
        );

        foreach (
            $relations->xpath('/pkg:Relationships/pkg:Relationship')
            as $relation
        ) {
            if ((string) $relation['Id'] !== $relationshipId) {
                continue;
            }

            $target = str_replace(
                '\\',
                '/',
                (string) $relation['Target']
            );

            $path = str_starts_with($target, '/')
                ? ltrim($target, '/')
                : (
                    str_starts_with($target, 'xl/')
                    ? $target
                    : 'xl/' . $target
                );

            $segments = [];

            foreach (explode('/', $path) as $segment) {
                if ($segment === '..') {
                    array_pop($segments);
                } elseif ($segment !== '' && $segment !== '.') {
                    $segments[] = $segment;
                }
            }

            return implode('/', $segments);
        }

        throw new RuntimeException(
            'No fue posible localizar la primera hoja de Excel.'
        );
    }

    private function columnToNumber(string $column): int
    {
        $number = 0;

        foreach (str_split(strtoupper($column)) as $letter) {
            $number = $number * 26 + ord($letter) - 64;
        }

        return $number;
    }

    private function numberToColumn(int $number): string
    {
        $column = '';

        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $column = chr(65 + $remainder) . $column;
            $number = intdiv($number - 1, 26);
        }

        return $column ?: 'A';
    }
}
