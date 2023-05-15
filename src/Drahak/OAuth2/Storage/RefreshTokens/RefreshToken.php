<?php

namespace Drahak\OAuth2\Storage\RefreshTokens;

use DateTime;

/**
 * RefreshToken
 * @package Drahak\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
class RefreshToken implements IRefreshToken {
    public function __construct(
        private readonly string     $refreshToken,
        private readonly DateTime   $expires,
        private readonly int|string $clientId,
        private readonly int|string $userId
    ) {
    }

    /**
     * Get refresh token
     */
    public function getRefreshToken(): string {
        return $this->refreshToken;
    }

    /**
     * Get expire time
     */
    public function getExpires(): DateTime {
        return $this->expires;
    }

    /**
     * Get client id
     */
    public function getClientId(): int|string {
        return $this->clientId;
    }

    public function getUserId(): int|string {
        return $this->userId;
    }
}
