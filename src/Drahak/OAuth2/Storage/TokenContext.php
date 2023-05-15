<?php

namespace Drahak\OAuth2\Storage;

use Drahak\OAuth2\Exceptions\InvalidStateException;

/**
 * TokenContext
 * @package Drahak\OAuth2\Token
 * @author Drahomír Hanák
 */
class TokenContext {
    private array $tokens = [];

    /**
     * Add identifier to collection
     */
    public function addToken(ITokenFacade $token): void {
        $this->tokens[$token->getIdentifier()] = $token;
    }

    /**
     * Get token
     *
     * @throws InvalidStateException
     */
    public function getToken(string $identifier): ITokenFacade {
        if (!isset($this->tokens[$identifier])) {
            throw new InvalidStateException('Token called "' . $identifier . '" not found in Token context');
        }

        return $this->tokens[$identifier];
    }
}
