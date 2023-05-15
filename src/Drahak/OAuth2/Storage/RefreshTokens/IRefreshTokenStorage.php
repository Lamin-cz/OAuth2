<?php

namespace Drahak\OAuth2\Storage\RefreshTokens;

/**
 * IRefreshTokenStorage
 * @package Drahak\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
interface IRefreshTokenStorage {

    /**
     * Store refresh token entity
     */
    public function store(IRefreshToken $refreshToken): void;

    /**
     * Remove refresh token
     */
    public function remove(string $refreshToken): void;

    /**
     * Validate refresh token
     */
    public function getValidRefreshToken(string $refreshToken): ?IRefreshToken;

}
