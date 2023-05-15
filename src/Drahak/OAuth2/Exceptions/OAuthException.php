<?php

namespace Drahak\OAuth2\Exceptions;

/**
 * OAuthException
 * @package Drahak\OAuth2\Application
 * @author Drahomír Hanák
 */
class OAuthException extends \Exception {
    protected string $key = '';

    /**
     * Get OAuth2 exception key as defined in specification
     */
    public function getKey(): string {
        return $this->key;
    }
}
