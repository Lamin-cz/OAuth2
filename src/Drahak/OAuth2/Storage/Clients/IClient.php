<?php

namespace Drahak\OAuth2\Storage\Clients;

/**
 * OAuth2 client entity
 * @package Drahak\OAuth2\Storage\Entity
 * @author Drahomír Hanák
 */
interface IClient {

    /**
     * Get client id
     */
    public function getId(): int|string;

    /**
     * Get client secret code
     */
    public function getSecret(): string;

    /**
     * Get client redirect URL
     */
    public function getRedirectUrl(): string;

}
