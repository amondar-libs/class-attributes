<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Enums;

enum LoadType: int
{
    case SimpleClass = 1;
    case RepeatableClass = 2;
    case Method = 3;

}
