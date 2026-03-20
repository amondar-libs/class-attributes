<?php

declare(strict_types = 1);

namespace Amondar\ClassAttributes\Enums;

use ReflectionClass;
use ReflectionClassConstant;
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
    case parameter = 'parameter';
    case function = 'function';

    public static function detectFromReflection(ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionClassConstant|ReflectionParameter|ReflectionFunction $reflection): self
    {
        return match (true) {
            $reflection instanceof ReflectionClass          => self::onClass,
            $reflection instanceof ReflectionMethod         => self::method,
            $reflection instanceof ReflectionProperty       => self::property,
            $reflection instanceof ReflectionParameter      => self::parameter,
            $reflection instanceof ReflectionClassConstant  => self::constant,
            $reflection instanceof ReflectionFunction       => self::function,
        };
    }
}
