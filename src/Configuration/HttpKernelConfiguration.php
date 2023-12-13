<?php

namespace Thor\Http\Configuration;

use ReflectionException;
use Thor\Http\Routing\Route;
use Thor\Http\Request\HttpMethod;
use Thor\Http\Routing\RouteAttributeReader;
use Thor\Http\Security\Authorization\Authorization;
use Thor\Common\Configuration\ConfigurationFromFile;

final class HttpKernelConfiguration extends ConfigurationFromFile
{
    /**
     * @return Route[]
     *
     * @throws ReflectionException
     */
    public function getRoutes(): array
    {
        $routes = [];
        foreach ($this['routes'] as $routeName => $routeInfo) {
            $routes += self::routesFromConfiguration($routeName, $routeInfo);
        }
        return $routes;
    }

    /**
     * @param string       $routeName
     * @param array|string $routeInfo
     *
     * @return Route[]
     *
     * @throws ReflectionException
     */
    public static function routesFromConfiguration(string $routeName, array|string $routeInfo): array
    {
        if ($routeName === 'load') {
            return RouteAttributeReader::readAttributesForClass([], $routeInfo);
        }
        return [
            new Route(
                $routeName,
                $routeInfo['path'] ?? '',
                HttpMethod::tryFrom($routeInfo['method']) ?? HttpMethod::GET,
                $routeInfo['parameters'] ?? [],
                $routeInfo['class'] ?? null,
                $routeInfo['method'] ?? null,
                isset($routeInfo['authorization']) ? new Authorization($routeInfo['authorization']) : null
            ),
        ];
    }

}
