<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Enums;

use ReflectionClass;
use ReflectionConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

enum Target: string
{
    case onClass = 'class';
    case method = 'method';
    case property = 'property';
    case constant = 'constant';
    case function = 'function';
    case parameter = 'parameter';

    public static function detectFromReflection(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionConstant|ReflectionParameter|ReflectionFunction $reflection): self
    {
        return match (true) {
            $reflection instanceof ReflectionClass     => self::onClass,
            $reflection instanceof ReflectionMethod    => self::method,
            $reflection instanceof ReflectionProperty  => self::property,
            $reflection instanceof ReflectionParameter => self::parameter,
            $reflection instanceof ReflectionConstant  => self::constant,
            $reflection instanceof ReflectionFunction  => self::function,
        };
    }
}
