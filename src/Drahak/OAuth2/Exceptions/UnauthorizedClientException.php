<?php

namespace Drahak\OAuth2\Exceptions;

/**
 * UnauthorizedClientException
 * @package Drahak\OAuth2\Application
 * @author Drahomír Hanák
 */
class UnauthorizedClientException extends OAuthException {
    protected string $key = 'unauthorized_client';

    public function __construct(
        string     $message = 'The grant type is not authorized for this client',
        \Exception $previous = null
    ) {
        parent::__construct($message, 401, $previous);
    }
}
