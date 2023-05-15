<?php

namespace Drahak\OAuth2\Storage\AccessTokens;

use Drahak\OAuth2\Exceptions\InvalidScopeException;

/**
 * Access token storage interface
 * @package Drahak\OAuth2\Storage
 * @author Drahomír Hanák
 */
interface IAccessTokenStorage {

    /**
     * Store access token to given client access entity
     * @throws InvalidScopeException
     */
    public function store(IAccessToken $accessToken);

    /**
     * Remove access token from access entity
     */
    public function remove(string $accessToken): void;

    /**
     * Get valid access token
     */
    public function getValidAccessToken(string $accessToken): ?IAccessToken;

}
