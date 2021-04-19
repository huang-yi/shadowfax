<?php

namespace HuangYi\Shadowfax\Laravel;

use ReflectionNamedType;
use ReflectionObject;

class InjectableMethodToClosure
{
    /**
     * Perform the transformation.
     *
     * @param  object  $object
     * @param  string  $method
     * @return \Closure
     * @throws \ReflectionException
     */
    public static function transform(object $object, string $method)
    {
        $refObject = new ReflectionObject($object);
        $refMethod = $refObject->getMethod($method);

        $params = [];

        foreach ($refMethod->getParameters() as $param) {
            if (! $param->getType() instanceof ReflectionNamedType) {
                $params[] = '$'.$param->name;
            } elseif ($param->isVariadic()) {
                $params[] = $param->getType()->getName().' ...$'.$param->name;
            } else {
                $params[] = $param->getType()->getName().' $'.$param->name;
            }
        }

        $closure = '$closure = function ('.implode(', ', $params).') use ($object, $method) {
            return $object->$method(...func_get_args());
        };';

        eval($closure);

        return $closure;
    }
}
