<?php

namespace Drahak\OAuth2\Storage\RefreshTokens;

use DateTime;

/**
 * IRefreshToken entity
 * @package Drahak\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
interface IRefreshToken {

    /**
     * Get refresh token
     */
    public function getRefreshToken(): string;

    /**
     * Get expire time
     */
    public function getExpires(): DateTime;

    /**
     * Get client id
     */
    public function getClientId(): int|string;

    /**
     * Get refresh token user ID
     */
    public function getUserId(): int|string;

}
