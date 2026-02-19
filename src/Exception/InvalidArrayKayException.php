<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Exception;

use ZJKiza\FlatMapper\Contract\ExceptionInterface;

final class InvalidArrayKayException extends \RuntimeException implements ExceptionInterface
{
    use ThrowIfTrait;
}
