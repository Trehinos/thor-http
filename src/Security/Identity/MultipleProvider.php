<?php

namespace Thor\Http\Security\Identity;

/**
 * Gives a way to look for an Identity in multiple Providers.
 *
 * @package          Thor/Security
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
readonly class MultipleProvider implements ProviderInterface
{

    /**
     * @param ProviderInterface[] $providers
     */
    public function __construct(private array $providers)
    {
    }

    /**
     * @inheritDoc
     */
    public function getIdentity(string $identifier): ?IdentityInterface
    {
        foreach ($this->providers as $provider) {
            $identity = $provider->getIdentity($identifier);
            if (null !== $identity) {
                return $identity;
            }
        }
        return null;
    }
}

