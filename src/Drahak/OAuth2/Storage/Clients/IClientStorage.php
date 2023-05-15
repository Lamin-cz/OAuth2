<?php

namespace Drahak\OAuth2\Storage\Clients;

/**
 * Client manager interface
 * @package Drahak\OAuth2\DataSource
 * @author Drahomír Hanák
 */
interface IClientStorage {

    /**
     * Get client data
     */
    public function getClient(int|string $clientId, string $clientSecret = null): ?IClient;

    /**
     * Can client use given grant type
     */
    public function canUseGrantType(string $clientId, string $grantType): bool;

}
