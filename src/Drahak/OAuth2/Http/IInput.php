<?php

namespace Drahak\OAuth2\Http;

/**
 * Request input data interface
 * @package Drahak\OAuth2\Http
 * @author Drahomír Hanák
 */
interface IInput {

    /**
     * Get all parameters
     */
    public function getParameters(): array;

    /**
     * Get single parameter value by name
     */
    public function getParameter(string $name): string|int|null;

    /**
     * Get authorization token
     */
    public function getAuthorization(): ?string;

}
