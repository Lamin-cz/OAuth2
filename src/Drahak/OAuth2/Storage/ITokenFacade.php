<?php

namespace Drahak\OAuth2\Storage;

use Drahak\OAuth2\Storage\AccessTokens\IAccessToken;
use Drahak\OAuth2\Storage\AuthorizationCodes\IAuthorizationCode;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\RefreshTokens\IRefreshToken;

/**
 * ITokenFacade
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 */
interface ITokenFacade {

    /** Default token names as defined in specification */
    public const ACCESS_TOKEN = 'access_token';
    public const REFRESH_TOKEN = 'refresh_token';
    public const AUTHORIZATION_CODE = 'authorization_code';

    /**
     * Create token
     */
    public function create(IClient $client, int|string $userId, array $scope = []): mixed;

    /**
     * Returns token entity
     */
    public function getEntity(string $token): IAccessToken|IRefreshToken|IAuthorizationCode;

    /**
     * Get token identifier name
     */
    public function getIdentifier(): string;

}
