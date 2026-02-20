<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaTestDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaTestLazyDto;
use ZJKiza\FlatMapper\Tests\Resources\Dto\MediaTestScalarDto;
use ZJKiza\FlatMapper\UniversalDtoMapper;

\class_exists(UniversalDtoMapper::class);
\class_exists(MediaTestDto::class);
\class_exists(MediaTestLazyDto::class);
\class_exists(MediaTestScalarDto::class);

/**
 * Generate fake flat SQL result
 *
 * @return array<int, array<string, scalar|null>>
 */
function generateRows(int $rows): array
{
    $data = [];

    $countAuthors = (int)($rows / 10);

    $authors = [];
    for ($i = 1; $i <= $countAuthors; $i++) {
        $authors [$i] = [
            'media_author_first_name' => 'First name '.$i,
            'media_author_last_name' => 'Last name '.$i,
        ];
    }

    $tags = [];
    for ($i = 1; $i <= $countAuthors; $i++) {
        $tags [$i] = [
            'media_tag_name' => 'Name '.$i,
        ];
    }

    $images = [];
    for ($i = 1; $i <= $countAuthors; $i++) {
        $images [$i] = [
            'media_image_name' => 'Name '.$i,
        ];
    }

    for ($i = 1; $i <= $rows; $i++) {

        $idImage = \random_int(1, $countAuthors);

        $id = \random_int(1, $countAuthors);
        $data[] = [
            'media_id' => (string)$i,
            'media_title' => 'Some title, lorem ipsum dolor sit amet '. $i,
            'media_description' => 'Some description, lorem ipsum dolor sit amet' .$i,
            'media_author_id' => (string)$id,
            'media_author_first_name' => $authors[$id]['media_author_first_name'],
            'media_author_last_name' =>  $authors[$id]['media_author_last_name'],
            'media_tag_id' => (string)$id,
            'media_tag_name' => $tags[$id]['media_tag_name'],
            'media_image_id' => (string)$idImage,
            'media_image_name' => $images[$idImage]['media_image_name'],
        ];

        $id = \random_int(1, $countAuthors);
        $data[] = [
            'media_id' => (string)$i,
            'media_title' => 'Some title, lorem ipsum dolor sit amet '. $i,
            'media_description' => 'Some description, lorem ipsum dolor sit amet' .$i,
            'media_author_id' => (string)$id,
            'media_author_first_name' => $authors[$id]['media_author_first_name'],
            'media_author_last_name' =>  $authors[$id]['media_author_last_name'],
            'media_tag_id' => (string)$id,
            'media_tag_name' => $tags[$id]['media_tag_name'],
            'media_image_id' => (string)$idImage,
            'media_image_name' => $images[$idImage]['media_image_name'],
        ];

        $id = \random_int(1, $countAuthors);
        $data[] = [
            'media_id' => (string)$i,
            'media_title' => 'Some title, lorem ipsum dolor sit amet '. $i,
            'media_description' => 'Some description, lorem ipsum dolor sit amet' .$i,
            'media_author_id' => (string)$id,
            'media_author_first_name' => $authors[$id]['media_author_first_name'],
            'media_author_last_name' =>  $authors[$id]['media_author_last_name'],
            'media_tag_id' => (string)$id,
            'media_tag_name' => $tags[$id]['media_tag_name'],
            'media_image_id' => (string)$idImage,
            'media_image_name' => $images[$idImage]['media_image_name'],
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
    /** @psalm-suppress InvalidOperand */
    $memory = (float)(\memory_get_usage(true) - $startMemory) / 1024 / 1024;

    \printf(
        "%s\nTime: %.4f s | Memory: %.2f MB\n\n",
        $label,
        $duration,
        $memory
    );
}

$items = 5000;

$rows = \generateRows($items);

// 15000 rows to process (5000 media, 500 tags, 500 authors and 500 images)
// 5000 media with 1 object image and 2 relations of 3 rows of authors and tags.

\benchmark(static function () use ($rows): void {
    $mapper = new UniversalDtoMapper();
    $mapper->map($rows, MediaTestScalarDto::class, 'media_id');
}, 'UniversalDtoMapper only scalar');
// Time: 0.126 s | Memory: 2.00 MB

\benchmark(static function () use ($rows): void {
    $mapper = new UniversalDtoMapper();
    $mapper->map($rows, MediaTestDto::class, 'media_id');
}, 'UniversalDtoMapper with 1 object image and 2 relations of 3 rows of authors and images');
// Time: 0.7882 s | Memory: 6.00 M

\benchmark(static function () use ($rows): void {
    $mapper = new UniversalDtoMapper();
    $mapper->map($rows, MediaTestLazyDto::class, 'media_id');
}, 'UniversalDtoMapper with 1 object image and 2 lazy relations of 3 rows of authors and images');
// Time: 0.410 s | Memory: 14.00 MB
