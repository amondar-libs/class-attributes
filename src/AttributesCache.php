<?php

namespace Amondar\ClassAttributes;

use Amondar\ClassAttributes\Contracts\AttributesCacheContract;
use Illuminate\Support\Facades\Facade;

/**
 * Class AttributesCache
 *
 * @method static void addNamespace( array $namespaces ) - Load attributes form given namespaces.
 * @method static void load() - Load attributes form stored namespaces.
 *
 * @see    \Amondar\ClassAttributes\Libraries\AttributesCache
 *
 * @author Amondar-SO
 */
class AttributesCache extends Facade
{

    protected static function getFacadeAccessor()
    {
        return AttributesCacheContract::class;
    }

}