<?php

namespace Drahak\OAuth2\Storage\RefreshTokens;

use DateTime;
use Drahak\OAuth2\IKeyGenerator;
use Drahak\OAuth2\Storage\AccessTokens\IAccessToken;
use Drahak\OAuth2\Storage\ITokenFacade;
use Drahak\OAuth2\Storage\Exceptions\InvalidRefreshTokenException;
use Drahak\OAuth2\Storage\Clients\IClient;

/**
 * RefreshToken
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 *
 * @property-read int $lifetime
 * @property-read IRefreshTokenStorage $storage
 */
class RefreshTokenFacade implements ITokenFacade {
    public function __construct(
        private readonly int $lifetime,
        private readonly IKeyGenerator $keyGenerator,
        private readonly IRefreshTokenStorage $storage
    ) {
    }

    /**
     * Create new refresh token
     */
    public function create(IClient $client, int|string $userId, array $scope = []): RefreshToken {
        $expires = new DateTime();
        $expires->modify('+' . $this->lifetime . ' seconds');
        $refreshToken = new RefreshToken(
            $this->keyGenerator->generate(),
            $expires,
            $client->getId(),
            $userId
        );
        $this->storage->store($refreshToken);

        return $refreshToken;
    }

    /**
     * Get refresh token entity
     *
     * @throws InvalidRefreshTokenException
     */
    public function getEntity(string $token): IRefreshToken {
        $entity = $this->storage->getValidRefreshToken($token);
        if (!$entity) {
            $this->storage->remove($token);
            throw new InvalidRefreshTokenException();
        }
        return $entity;
    }

    /**
     * Get token identifier name
     */
    public function getIdentifier(): string {
        return self::REFRESH_TOKEN;
    }


    /****************** Getters & setters ******************/

    /**
     * Get token lifetime
     */
    public function getLifetime(): int {
        return $this->lifetime;
    }

    /**
     * Get storage
     */
    public function getStorage(): IRefreshTokenStorage {
        return $this->storage;
    }
}
