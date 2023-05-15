<?php

namespace Drahak\OAuth2\Storage\AuthorizationCodes;

use DateTime;

/**
 * IAuthorizationCode
 * @package Drahak\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
interface IAuthorizationCode {

    /**
     * Get authorization code
     */
    public function getAccessToken(): string;

    /**
     * Set expire date
     */
    public function getExpires(): DateTime;

    /**
     * Get client ID
     */
    public function getClientId(): int|string;

    /**
     * Get user ID
     */
    public function getUserId(): int|string;

    /**
     * Get scope
     */
    public function getScope(): array;

}
