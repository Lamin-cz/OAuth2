<?php

namespace Drahak\OAuth2\Storage\Clients;

/**
 * OAuth2 base client caret
 * @package Drahak\OAuth2\Storage\Entity
 * @author Drahomír Hanák
 */
class Client implements IClient {
    public function __construct(
        private readonly int|string $id,
        private readonly string     $secret,
        private readonly string     $redirectUrl
    ) {
    }

    public function getId(): int|string {
        return $this->id;
    }

    public function getRedirectUrl(): string {
        return $this->redirectUrl;
    }

    public function getSecret(): string {
        return $this->secret;
    }
}
