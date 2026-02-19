<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Exception;

use ZJKiza\FlatMapper\Contract\ExceptionInterface;

final class InvalidArgumentException extends \RuntimeException implements ExceptionInterface
{
}
