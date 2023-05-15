<?php

namespace Drahak\OAuth2\Grant;

use Drahak\OAuth2\Storage;
use Drahak\OAuth2\Storage\AccessTokens\IAccessToken;
use Drahak\OAuth2\Storage\ITokenFacade;

/**
 * AuthorizationCode
 * @package Drahak\OAuth2\Grant
 * @author Drahomír Hanák
 */
class AuthorizationCode extends GrantType {
    private array $scope = [];
    private IAccessToken $entity;

    protected function getScope(): array {
        return $this->scope;
    }

    /**
     * Get authorization code identifier
     */
    public function getIdentifier(): string {
        return self::AUTHORIZATION_CODE;
    }

    /**
     * Verify request
     */
    protected function verifyRequest(): void {
        $code = $this->input->getParameter('code');

        $this->entity = $this->token->getToken(ITokenFacade::AUTHORIZATION_CODE)->getEntity($code);
        $this->scope = $this->entity->getScope();

        $this->token->getToken(ITokenFacade::AUTHORIZATION_CODE)->getAccessToken()->remove($code);
    }

    /**
     * Generate access token
     */
    protected function generateAccessToken(): array {
        $client = $this->getClient();
        $accessTokenStorage = $this->token->getToken(ITokenFacade::ACCESS_TOKEN);
        $refreshTokenStorage = $this->token->getToken(ITokenFacade::REFRESH_TOKEN);

        $accessToken = $accessTokenStorage->create(
            $client,
            $this->user->getId() ?: $this->entity->getUserId(),
            $this->getScope()
        );
        $refreshToken = $refreshTokenStorage->create(
            $client,
            $this->user->getId() ?: $this->entity->getUserId(),
            $this->getScope()
        );

        return [
            'access_token' => $accessToken->getAccessToken(),
            'token_type' => 'bearer',
            'expires_in' => $accessTokenStorage->getLifetime(),
            'refresh_token' => $refreshToken->getRefreshToken(),
        ];
    }

}
