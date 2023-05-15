<?php

namespace Drahak\OAuth2\Storage\AccessTokens;

use DateTime;

/**
 * IAccessToken entity
 * @package Drahak\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 */
interface IAccessToken {

    /**
     * Get access token
     */
    public function getAccessToken(): string;

    /**
     * Get expires time
     */
    public function getExpires(): DateTime;

    /**
     * Get client ID
     */
    public function getClientId(): int|string;

    /**
     * Get access token user ID
     */
    public function getUserId(): int|string;

    /**
     * Get scope
     */
    public function getScope(): array;

}
