<?php

namespace Drahak\OAuth2\Storage\AuthorizationCodes;

use DateTime;
use Drahak\OAuth2\IKeyGenerator;
use Drahak\OAuth2\Storage\ITokenFacade;
use Drahak\OAuth2\Storage\Exceptions\InvalidAuthorizationCodeException;
use Drahak\OAuth2\Storage\Clients\IClient;

/**
 * AuthorizationCode
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 */
class AuthorizationCodeFacade implements ITokenFacade {
    public function __construct(
        private readonly int                       $lifetime,
        private readonly IKeyGenerator             $keyGenerator,
        private readonly IAuthorizationCodeStorage $storage
    ) {
    }

    /**
     * Create authorization code
     */
    public function create(IClient $client, int|string $userId, array $scope = []): AuthorizationCode {
        $accessExpires = new DateTime();
        $accessExpires->modify('+' . $this->lifetime . ' seconds');

        $authorizationCode = new AuthorizationCode(
            $this->keyGenerator->generate(),
            $accessExpires,
            $client->getId(),
            $userId,
            $scope
        );
        $this->storage->store($authorizationCode);

        return $authorizationCode;
    }

    /**
     * Get authorization code entity
     * @throws InvalidAuthorizationCodeException
     */
    public function getEntity(string $token): IAuthorizationCode {
        $entity = $this->storage->getValidAuthorizationCode($token);
        if (!$entity) {
            $this->storage->remove($token);
            throw new InvalidAuthorizationCodeException();
        }
        return $entity;
    }

    /**
     * Get token identifier name
     */
    public function getIdentifier(): string {
        return self::AUTHORIZATION_CODE;
    }


    /****************** Getters & setters ******************/

    /**
     * Get token lifetime
     * @return int
     */
    public function getLifetime(): int {
        return $this->lifetime;
    }

    /**
     * Get storage
     */
    public function getStorage(): IAuthorizationCodeStorage {
        return $this->storage;
    }

}
