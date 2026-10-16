<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface ExcelSourceImporter
{
    public function requiredHeadings(): array;

    public function import(Collection $rows, int $versionId): int;
}
