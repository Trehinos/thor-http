<?php

namespace Thor\Http\Server;

use InvalidArgumentException;
use Thor\Common\Configuration\Configuration;
use Thor\Database\PdoExtension\Handler;
use Thor\Database\PdoExtension\PdoCollection;
use Thor\Database\PdoExtension\Requester;
use JetBrains\PhpStorm\ExpectedValues;
use Thor\Http\Response\ResponseFactory;
use Thor\Http\{Security\SecurityInterface,
    Uri,
    UriInterface,
    Routing\Route,
    Routing\Router,
    Response\HttpStatus,
    Response\ResponseInterface,
    Request\ServerRequestInterface};

/**
 * Handles a ServerRequestInterface and send a ResponseInterface.
 *
 * @package          Thor/Http/Server
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class HttpServer implements RequestHandlerInterface
{

    private ?ServerRequestInterface $request = null;

    /**
     * @param Router                 $router
     * @param SecurityInterface|null $security
     * @param PdoCollection          $pdoCollection
     * @param Configuration          $language
     */
    public function __construct(
        private Router $router,
        private ?SecurityInterface $security,
        private PdoCollection $pdoCollection,
        private Configuration $language
    ) {
    }

    /**
     * Gets the served request.
     *
     * @return ServerRequestInterface|null
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $route = $this->route($request);
        if (false === $route) {
            return ResponseFactory::methodNotAllowed($this->router->getErrorRoute()->getAllowedMethods());
        }
        if (null === $route) {
            return ResponseFactory::notFound();
        }

        $controllerHandler = new ControllerHandler($this, $route);
        return $controllerHandler->handle($request);
    }

    /**
     * Uses the router to return the Route corresponding the $request Request.
     *
     * Returns `false` if the HttpMethod is invalid.
     * The route will be retrievable from outside context with `$server->getRouter()->errorRoute`.
     *
     * Returns `null` if no Route was found for this request.
     * In this case, `$server->getRouter()->errorRoute` stays null.
     *
     * @param ServerRequestInterface $request
     *
     * @return Route|false|null
     */
    public function route(ServerRequestInterface $request): Route|false|null
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'localhost';

        return $this->router->match($request);
    }

    /**
     * Gets the router of this server.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Gets a new PdoRequester corresponding the handler $name in this server's PdoCollection.
     *
     * If no PdoHandler is found, returns `null`.
     *
     * @param string $name
     *
     * @return Requester|null
     */
    public function getRequester(string $name = 'default'): ?Requester
    {
        return null !== ($handler = $this->getHandler($name)) ? new Requester($handler) : null;
    }

    /**
     * Gets the handler from $name in this server's PdoCollection.
     *
     * If no PdoHandler is found, returns `null`.
     *
     * @param string $name
     *
     * @return Requester|null
     */
    public function getHandler(string $name = 'default'): ?Handler
    {
        return $this->pdoCollection->get($name);
    }

    /**
     * Gets the PdoCollection.
     *
     * @return PdoCollection
     */
    public function getPdos(): PdoCollection
    {
        return $this->pdoCollection;
    }

    /**
     * Sets the security context of the server.
     */
    public function setSecurity(SecurityInterface|null $security): void
    {
        $this->security = $security;
    }

    /**
     * Returns the security context of the server.
     *
     * @return SecurityInterface|null
     */
    public function getSecurity(): ?SecurityInterface
    {
        return $this->security;
    }

    /**
     * Gets the dictionary of the server.
     *
     * @return array
     */
    public function getLanguage(): array
    {
        return $this->language->getArrayCopy();
    }

    /**
     * Returns a redirect Response depending on the routeName.
     *
     * @param string     $routeName
     * @param array      $params
     * @param array      $query
     * @param HttpStatus $status
     *
     * @return ResponseInterface
     */
    public function redirect(
        string $routeName,
        array $params = [],
        array $query = [],
        #[ExpectedValues([
            HttpStatus::FOUND,
            HttpStatus::SEE_OTHER,
            HttpStatus::TEMPORARY_REDIRECT,
            HttpStatus::PERMANENT_REDIRECT,
        ])]
        HttpStatus $status = HttpStatus::FOUND
    ): ResponseInterface {
        return $this->redirectTo($this->generateUrl($routeName, $params, $query), $status);
    }

    /**
     * Returns a redirect Response to the specified Uri.
     *
     * @param UriInterface $uri
     * @param HttpStatus   $status
     *
     * @return ResponseInterface
     */
    public function redirectTo(
        UriInterface $uri,
        #[ExpectedValues([
            HttpStatus::FOUND,
            HttpStatus::SEE_OTHER,
            HttpStatus::TEMPORARY_REDIRECT,
            HttpStatus::PERMANENT_REDIRECT,
        ])]
        HttpStatus $status = HttpStatus::FOUND
    ): ResponseInterface {
        return match ($status) {
            HttpStatus::FOUND              => ResponseFactory::found($uri),
            HttpStatus::SEE_OTHER          => ResponseFactory::seeOther($uri),
            HttpStatus::TEMPORARY_REDIRECT => ResponseFactory::temporaryRedirect($uri),
            HttpStatus::PERMANENT_REDIRECT => ResponseFactory::permanentRedirect($uri),
            default                        => throw new InvalidArgumentException()
        };
    }

    /**
     * Generates an Uri from the routeName.
     *
     * @param string $routeName
     * @param array  $params
     * @param array  $query
     *
     * @return UriInterface
     */
    public function generateUrl(string $routeName, array $params = [], array $query = []): UriInterface
    {
        if ($this->router->getRoute($routeName) === null) {
            return Uri::create("#generate-url-error");
        }
        return $this->router->getUrl($routeName, $params, $query);
    }
}
