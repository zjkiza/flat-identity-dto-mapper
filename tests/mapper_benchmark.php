<?php

declare(strict_types=1);


require __DIR__ . '/../../vendor/autoload.php';

use App\FlatMapper\UniversalDtoMapper;
use App\Dto\ArrayMediaDto;

/**
 * Generate fake flat SQL result
 */
function generateRows(int $rows): array
{
    $data = [];

    for ($i = 1; $i <= $rows; $i++) {
        $data[] = [
            'media_id' => 1,
            'media_title' => 'Some title',
            'media_author_id' => $i,
            'media_author_name' => 'Author ' . $i,
        ];
    }

    return $data;
}

function benchmark(callable $fn, string $label): void
{
    $startMemory = \memory_get_usage(true);
    $startTime = \microtime(true);

    $fn();

    $duration = \microtime(true) - $startTime;
    $memory = \memory_get_usage(true) - $startMemory;

    \printf(
        "%s\nTime: %.4f s | Memory: %.2f MB\n\n",
        $label,
        $duration,
        $memory / 1024 / 1024
    );
}

$rows = \generateRows(10_000);

\benchmark(function () use ($rows) {
    $mapper = new UniversalDtoMapper();
    $mapper->map($rows, ArrayMediaDto::class, 'media_id');
}, 'UniversalDtoMapper (Identity Map)');

\benchmark(function () use ($rows) {
    $dto = new ArrayMediaDto();
    foreach ($rows as $row) {
        $dto->author[] = $row['media_author_name'];
    }
}, 'Naive hydration');
