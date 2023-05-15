<?php

namespace Drahak\OAuth2\Storage\AuthorizationCodes;

use DateTime;

/**
 * Base AuthorizationCode entity
 * @package Drahak\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
class AuthorizationCode implements IAuthorizationCode {
    public function __construct(
        private readonly string     $accessToken,
        private readonly DateTime   $expires,
        private readonly int|string $clientId,
        private readonly int|string $userId,
        private readonly array      $scope
    ) {
    }

    public function getAccessToken(): string {
        return $this->accessToken;
    }

    public function getClientId(): int|string {
        return $this->clientId;
    }

    public function getUserId(): int|string {
        return $this->userId;
    }

    public function getExpires(): DateTime {
        return $this->expires;
    }

    public function getScope(): array {
        return $this->scope;
    }
}
