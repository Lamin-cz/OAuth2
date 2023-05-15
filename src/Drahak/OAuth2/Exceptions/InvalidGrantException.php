<?php

namespace Drahak\OAuth2\Exceptions;

/**
 * InvalidGrantException is thrown when provided authorization grant (authorization code, resource owner credentials)
 * or refresh token is invalid, expired, revoked, does not match redirect URI used in authorization request
 * @package Drahak\OAuth2
 * @author Drahomír Hanák
 */
class InvalidGrantException extends OAuthException {
    protected string $key = 'invalid_grant';

    public function __construct(
        string     $message = 'Given grant token is invalid or expired',
        \Exception $previous = null
    ) {
        parent::__construct($message, 400, $previous);
    }
}
