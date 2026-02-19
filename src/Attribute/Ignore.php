<?php

declare(strict_types=1);

namespace ZJKiza\FlatMapper\Attribute;

use ZJKiza\FlatMapper\Contract\AttributeInterface;

/**
 * Ignored property.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Ignore implements AttributeInterface
{
}
