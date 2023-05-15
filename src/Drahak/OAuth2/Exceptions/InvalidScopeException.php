<?php

namespace Drahak\OAuth2\Exceptions;

/**
 * InvalidScopeException
 * @package Drahak\OAuth2
 * @author Drahomír Hanák
 */
class InvalidScopeException extends OAuthException {
    protected string $key = 'invalid_scope';

    public function __construct(string $message = 'Given scope does not exist', \Exception $previous = null) {
        parent::__construct($message, 400, $previous);
    }
}
