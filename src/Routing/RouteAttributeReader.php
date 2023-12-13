<?php

namespace Thor\Http\Routing;

use ReflectionClass;
use ReflectionMethod;
use ReflectionAttribute;
use ReflectionException;
use Thor\Http\Security\Authorization\Authorization;

final class RouteAttributeReader
{

    /**
     * @return Route[]
     * @throws ReflectionException
     */
    public static function readAttributesForClassList(array $routes, array $pathsList): array
    {
        foreach ($pathsList as $loadPath) {
            self::readAttributesForClass($routes, $loadPath);
        }
        return $routes;
    }

    /**
     * @return Route[]
     * @throws ReflectionException
     */
    public static function readAttributesForClass(array $routes, string $className): array
    {
        $rc = new ReflectionClass($className);
        foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            self::readAttributesForMethod($routes, $className, $method);
        }
        return $routes;
    }

    /**
     * @return Route[]
     */
    public static function readAttributesForMethod(array $routes, string $className, ReflectionMethod $method): array
    {
        if (!empty($routeAttrs = $method->getAttributes(Route::class))) {
            $authorization = ($method->getAttributes(Authorization::class)[0] ?? null)?->newInstance();
            foreach ($routeAttrs as $routeAttr) {
                self::instantiateFromAttributes($routes, $className, $method, $routeAttr, $authorization);
            }
        }
        return $routes;
    }

    /**
     * @return Route[]
     */
    public static function instantiateFromAttributes(array $routes, string $className, ReflectionMethod $method, ReflectionAttribute $routeAttr, ?Authorization $authorization): array
    {
        /** @var Route $route */
        $route = $routeAttr->newInstance();
        $route->setControllerClass($className);
        $route->setControllerMethod($method->getName());
        $route->authorization = $authorization;
        $routes[]             = $route;
        return $routes;
    }

}
