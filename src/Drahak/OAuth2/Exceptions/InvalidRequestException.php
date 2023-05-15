<?php

namespace Drahak\OAuth2\Exceptions;

/**
 * InvalidRequestException
 * @package Drahak\OAuth2\Application
 * @author Drahomír Hanák
 */
class InvalidRequestException extends OAuthException {
    protected string $key = 'invalid_request';

    public function __construct(string $message = 'Invalid request parameters', \Exception $previous = null) {
        parent::__construct($message, 400, $previous);
    }
}
