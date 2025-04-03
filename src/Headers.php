<?php

namespace Thor\Http;

use DateTimeInterface;
use DateTimeImmutable;
use Thor\Maybe\Option;

final class Headers
{

    public const AUTHORIZATION_BEARER = 'Bearer';
    public const TYPE_JSON = 'application/json; charset=UTF-8';
    public const TYPE_TEXT = 'text/plain';

    private array $headers = [];

    public function __construct()
    {
    }

    /**
     * @param array<string, string | string[]> $headers
     *
     * @return static
     */
    public static function create(array $headers = []): self
    {
        $headersObject = new self();
        $headersObject->headers = $headers;
        return $headersObject;
    }

    /**
     * Clear all headers in the internal array as a fluid interface.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->headers = [];
        return $this;
    }

    /**
     * @param string            $name
     * @param string | string[] $value
     *
     * @return $this
     */
    public function set(string $name, string|array $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return Option<string | string[]>
     */
    public function get(string $name): Option
    {
        if (!array_key_exists($name, $this->headers)) {
            return Option::none();
        }
        return Option::some($this->headers[$name]);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function host(string $host): self
    {
        return self::merge_with_array(['Host' => $host]);
    }

    /**
     * @param array $headersToAdd
     *
     * @return $this
     */
    public function merge_with_array(array $headersToAdd): self
    {
        foreach ($headersToAdd as $name => $value) {
            if (array_key_exists($name, $this->headers)) {
                if (is_array($this->headers[$name])) {
                    if (is_array($value)) {
                        $this->headers[$name] = array_merge($this->headers[$name], $value);
                    } else {
                        $this->headers[$name][] = $value;
                    }
                } else {
                    if (is_array($value)) {
                        $this->headers[$name] = array_merge([$this->headers[$name]], $value);
                    } else {
                        $this->headers[$name] = [$this->headers[$name], $value];
                    }
                }
                continue;
            }
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function merge(Headers $headers): self
    {
        return self::merge_with_array($headers->all());
    }

    /**
     * @param DateTimeInterface $dateTime
     *
     * @return $this
     */
    public function date(DateTimeInterface $dateTime = new DateTimeImmutable()): self
    {
        return self::merge_with_array(
            [
                'Date' => $dateTime->format(DateTimeInterface::RFC7231),
            ]
        );
    }

    /**
     * @param string $userAgent
     *
     * @return $this
     */
    public function userAgent(string $userAgent): self
    {
        return self::merge_with_array(['User-Agent' => $userAgent]);
    }

    /**
     * @param string $mimeType
     *
     * @return $this
     */
    public function contentType(string $mimeType): self
    {
        return self::merge_with_array(['Content-Type' => $mimeType]);
    }

    /**
     * @param int $length
     *
     * @return $this
     */
    public function contentLength(int $length): self
    {
        return self::merge_with_array(['Content-Length' => $length]);
    }

}
