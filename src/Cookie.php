<?php

namespace Thor\Http;

final class Cookie
{

    public const SAMESITE_NONE = 'None';
    public const SAMESITE_LAX = 'Lax';
    public const SAMESITE_STRICT = 'Strict';

    public static string|int $ttl = 3600;
    public static string $sameSite = self::SAMESITE_LAX;

    public static string $path = '';

    public static string $domain = '';
    public static bool $secure = true;
    public static bool $httpOnly = true;

    private function __construct()
    {
    }

    public static function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    private static function expires(): int
    {
        if (is_string(self::$ttl)) {
            return strtotime(self::$ttl);
        }
        return time() + self::$ttl;
    }

    public static function set(
        string $name,
        string $value = "",
    ): bool {
        return setcookie(
            $name,
            $value,
            [
                'expires' => self::expires(),
                'samesite' => self::$sameSite,
                'path' => self::$path,
                'domain' => self::$domain,
                'secure' => self::$secure,
                'httponly' => self::$httpOnly
            ],

        );
    }

    public static function setForSession(
        string $name,
        string $value = "",
    ): bool {
        return setcookie(
            $name,
            $value,
            [
                'samesite' => self::$sameSite,
                'path' => self::$path,
                'domain' => self::$domain,
                'secure' => self::$secure,
                'httponly' => self::$httpOnly
            ]
        );
    }

    public static function setRawForSession(
        string $name,
        string $value = "",
    ): bool {
        return setrawcookie(
            $name,
            $value,
            [
                'samesite' => self::$sameSite,
                'path' => self::$path,
                'domain' => self::$domain,
                'secure' => self::$secure,
                'httponly' => self::$httpOnly
            ]
        );
    }

    public static function delete(
        string $name,
    ): bool {
        return setcookie(
            $name,
            false,
            [
                'expires' => self::expires(),
                'samesite' => self::$sameSite,
                'path' => self::$path,
                'domain' => self::$domain,
                'secure' => self::$secure,
                'httponly' => self::$httpOnly
            ]
        );
    }

    public static function setRaw(
        string $name,
        string $value = "",
    ): bool {
        return setrawcookie(
            $name,
            $value,
            [
                'expires' => self::expires(),
                'samesite' => self::$sameSite,
                'path' => self::$path,
                'domain' => self::$domain,
                'secure' => self::$secure,
                'httponly' => self::$httpOnly
            ]
        );
    }

}
