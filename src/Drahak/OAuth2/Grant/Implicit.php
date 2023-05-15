<?php

namespace Drahak\OAuth2\Grant;

use Drahak\OAuth2\Storage\ITokenFacade;

/**
 * Implicit grant type
 * @package Drahak\OAuth2\Grant
 * @author Drahomír Hanák
 */
class Implicit extends GrantType {

    /**
     * Get identifier string to this grant type
     */
    public function getIdentifier(): string {
        return self::IMPLICIT;
    }

    /**
     * Verify grant type
     */
    protected function verifyGrantType(): void {
    }

    /**
     * Verify request
     */
    protected function verifyRequest(): void {
    }

    /**
     * Generate access token
     */
    protected function generateAccessToken(): array {
        $accessTokenStorage = $this->token->getToken(ITokenFacade::ACCESS_TOKEN);
        $accessToken = $accessTokenStorage->create($this->getClient(), $this->user->getId(), $this->getScope());

        return [
            'access_token' => $accessToken->getAccessToken(),
            'expires_in' => $accessTokenStorage->getLifetime(),
            'token_type' => 'bearer',
        ];
    }
}
