<?php

namespace Drahak\OAuth2\Storage\Dibi;

use DateTime;
use Drahak\OAuth2\Storage\RefreshTokens\IRefreshTokenStorage;
use Drahak\OAuth2\Storage\RefreshTokens\IRefreshToken;
use Drahak\OAuth2\Storage\RefreshTokens\RefreshToken;

/**
 * Nette database RefreshToken storage
 * @package Drahak\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
class RefreshTokenStorage implements IRefreshTokenStorage {
    public function __construct(private readonly \DibiConnection $context) {
        // TODO dibi
    }

    /**
     * Get authorization code table
     */
    protected function getTable(): string {
        return 'oauth_refresh_token';
    }

    /******************** IRefreshTokenStorage ********************/

    /**
     * Store refresh token
     */
    public function store(IRefreshToken $refreshToken): void {
        $this->context->insert($this->getTable(), [
            'refresh_token' => $refreshToken->getRefreshToken(),
            'client_id' => $refreshToken->getClientId(),
            'user_id' => $refreshToken->getUserId(),
            'expires_at' => $refreshToken->getExpires(),
        ])->execute();
    }

    /**
     * Remove refresh token
     */
    public function remove(string $refreshToken): void {
        $this->context->delete($this->getTable())->where(['refresh_token' => $refreshToken])->execute();
    }

    /**
     * Get valid refresh token
     */
    public function getValidRefreshToken(string $refreshToken): ?IRefreshToken {
        $row = $this->context->select('*')->from($this->getTable())
            ->where('refresh_token = %s', $refreshToken)
            ->where('TIMEDIFF(expires_at, NOW()) >= 0')
            ->fetch();

        if (!$row) {
            return null;
        }

        return new RefreshToken(
            $row['refresh_token'],
            new DateTime($row['expires_at']),
            $row['client_id'],
            $row['user_id']
        );
    }
}
