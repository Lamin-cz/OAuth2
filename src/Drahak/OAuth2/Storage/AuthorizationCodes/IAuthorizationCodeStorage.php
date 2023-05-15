<?php

namespace Drahak\OAuth2\Storage\AuthorizationCodes;

use Drahak\OAuth2\Exceptions\InvalidScopeException;

/**
 * IAuthorizationCodeStorage
 * @package Drahak\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
interface IAuthorizationCodeStorage {

    /**
     * Store authorization code
     * @throws InvalidScopeException
     */
    public function store(IAuthorizationCode $authorizationCode);

    /**
     * Remove authorization code
     */
    public function remove(string $authorizationCode): void;

    /**
     * Get valid authorization code
     */
    public function getValidAuthorizationCode(string $authorizationCode): ?IAuthorizationCode;

}
