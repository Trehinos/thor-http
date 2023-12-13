<?php

namespace Thor\Http;

use Thor\Common\Configuration\Configuration;
use Thor\Http\{Request\ServerRequestFactory,
    Server\HttpServer,
    Response\ResponseInterface,
    Request\ServerRequestInterface};
use Thor\Process\KernelInterface;

/**
 * HttpKernel of Thor. It is by default instantiated with the `api.php` entry point.
 *
 * @package          Thor/Http
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class HttpKernel implements KernelInterface
{

    /**
     * @param HttpServer $server
     */
    public function __construct(protected HttpServer $server)
    {
    }

    /**
     * This static function returns a new HttpKernel with specified configuration.
     *
     * @param Configuration $config
     *
     * @return static
     */
    public static function createFromConfiguration(Configuration $config): static
    {
        return new static($config['server']);
    }

    /**
     * This function exit the program if PHP is run from Cli context.
     *
     * @return void
     */
    final public static function guardHttp(): void
    {
        if ('cli' === php_sapi_name()) {
            exit;
        }
    }

    /**
     * This method executes the HttpKernel :
     * 1. Load a ServerRequestInterface from the globals,
     * 2. Let the HttpServer handle it (security, controller, etc),
     * 3. Uses the returned ResponseInterface to send the response.
     *
     * @see ServerRequestInterface
     * @see HttpServer
     * @see HttpController
     * @see ResponseInterface
     */
    final public function execute(): void
    {
        ob_start();
        $request = $this->alterRequest(ServerRequestFactory::createFromGlobals());
        $response = $this->alterResponse($this->handle($request));
        $responseStatus = $response->getStatus()->normalized();
        $responseCode = $response->getStatus()->value;

        ob_clean(); // Prevent accidental echoes

        http_response_code($responseCode);                                      // Emit status code

        if (!empty($headers = $response->getHeaders())) {
            foreach ($headers as $headerName => $headerValue) {                 // Emit headers
                if (is_array($headerValue)) {
                    foreach ($headerValue as $subValue) {
                        header("$headerName: $subValue", false);
                    }
                    continue;
                }
                header("$headerName: $headerValue");
            }
        }

        if ($request->getMethod()->responseHasBody() && ($body = $response->getBody()->getContents()) !== '') {
            echo $body;                                                         // Print body
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function alterResponse(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     */
    protected function alterRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request;
    }

    /**
     * Makes the HttpServer handle the ServerRequestInterface and returns its ResponseInterface.
     */
    public function handle(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $this->server->route($serverRequest);
        if ($this->server->getSecurity() !== null) {
            return $this->server->getSecurity()->process($serverRequest, $this->server);
        }
        return $this->server->handle($serverRequest);
    }

}
