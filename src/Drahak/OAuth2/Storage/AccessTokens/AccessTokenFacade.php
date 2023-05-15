<?php

namespace Drahak\OAuth2\Storage\AccessTokens;

use DateTime;
use Drahak\OAuth2\IKeyGenerator;
use Drahak\OAuth2\Exceptions\InvalidScopeException;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\Exceptions\InvalidAccessTokenException;
use Drahak\OAuth2\Storage\ITokenFacade;

/**
 * AccessToken
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 *
 */
class AccessTokenFacade implements ITokenFacade {
    public function __construct(
        private readonly int                 $lifetime,
        private readonly IKeyGenerator       $keyGenerator,
        private readonly IAccessTokenStorage $accessToken
    ) {
    }

    /**
     * Create access token
     * @param IClient $client
     * @param string|int $userId
     * @param array $scope
     * @return AccessToken
     * @throws InvalidScopeException
     */
    public function create(IClient $client, int|string $userId, array $scope = []): AccessToken {
        $accessExpires = new DateTime();
        $accessExpires->modify('+' . $this->lifetime . ' seconds');

        $accessToken = new AccessToken(
            $this->keyGenerator->generate(),
            $accessExpires,
            $client->getId(),
            $userId,
            $scope
        );
        $this->accessToken->store($accessToken);

        return $accessToken;
    }

    /**
     * Check access token
     * @throws InvalidAccessTokenException
     */
    public function getEntity(string $token): IAccessToken {
        $entity = $this->accessToken->getValidAccessToken($token);
        if (!$entity) {
            $this->accessToken->remove($token);
            throw new InvalidAccessTokenException();
        }
        return $entity;
    }

    /**
     * Get token identifier name
     */
    public function getIdentifier(): string {
        return self::ACCESS_TOKEN;
    }

    /******************** Getters & setters ********************/

    /**
     * Returns access token lifetime
     */
    public function getLifetime(): int {
        return $this->lifetime;
    }

    /**
     * Get access token storage
     */
    public function getAccessToken(): IAccessTokenStorage {
        return $this->accessToken;
    }
}
