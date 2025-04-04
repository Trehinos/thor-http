<?php

namespace Thor\Http\Server;

use Thor\Http\Routing\Route;
use Thor\Http\Response\ResponseInterface;
use Thor\Http\Request\ServerRequestInterface;

/**
 * Handles a request by instantiating a controller and sending its response.
 *
 * @package Thor/Http/Server
 * @copyright (2021) Sébastien Geldreich
 * @license MIT
 */
class ControllerHandler implements RequestHandlerInterface
{

    /**
     * @param HttpServer $server
     * @param Route      $route
     */
    public function __construct(private HttpServer $server, private Route $route)
    {
    }

    /**
     * Instantiate the controller class from the Route given in the constructor and returns its response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $cClass = $this->route->getControllerClass();
        $cMethod = $this->route->getControllerMethod();
        $controller = new $cClass($this->server);
        return $controller->$cMethod(...array_values($this->route->getFilledParams()));
    }
}
