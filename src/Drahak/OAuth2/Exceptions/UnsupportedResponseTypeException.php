<?php

namespace Drahak\OAuth2\Exceptions;

/**
 * UnsupportedResponseTypeException
 * @package Drahak\OAuth2
 * @author Drahomír Hanák
 */
class UnsupportedResponseTypeException extends OAuthException {
    protected string $key = 'unsupported_response_type';

    public function __construct(string $message = 'Grant type not supported', \Exception $previous = null) {
        parent::__construct($message, 400, $previous);
    }
}
