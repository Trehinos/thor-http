<?php

namespace Thor\Http\Security;

use Thor\Http\Security\Authentication\AuthenticatorInterface;
use Thor\Http\Security\Identity\IdentityInterface;
use Thor\Http\Security\Identity\ProviderInterface;
use Thor\Http\Server\MiddlewareInterface;

/**
 * Interface of a Security context of Thor.
 *
 * @package          Thor/Security
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
interface SecurityInterface extends MiddlewareInterface
{

    /**
     * Gets the firewalls of the security context.
     *
     * @return Firewall[]
     */
    public function getFirewalls(): array;

    /**
     * Gets the authenticator of this context.
     *
     * @return AuthenticatorInterface
     */
    public function getAuthenticator(): AuthenticatorInterface;

    /**
     * Gets the provider of this context.
     *
     * @return ProviderInterface
     */
    public function getProvider(): ProviderInterface;

    /**
     * Gets the current authenticated identity.
     *
     * @return IdentityInterface|null
     */
    public function getCurrentIdentity(): ?IdentityInterface;

}
