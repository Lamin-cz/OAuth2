<?php

namespace Drahak\OAuth2\Storage\NDB;

use DateTime;
use Drahak\OAuth2\Storage\RefreshTokens\IRefreshTokenStorage;
use Drahak\OAuth2\Storage\RefreshTokens\IRefreshToken;
use Drahak\OAuth2\Storage\RefreshTokens\RefreshToken;
use Nette\Database\Explorer;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\Selection;

/**
 * Nette database RefreshToken storage
 * @package Drahak\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
class RefreshTokenStorage implements IRefreshTokenStorage {
    public function __construct(private readonly Explorer $context) {
    }

    /**
     * Get authorization code table
     */
    protected function getTable(): Selection {
        return $this->context->table('oauth_refresh_token');
    }

    /******************** IRefreshTokenStorage ********************/

    /**
     * Store refresh token
     */
    public function store(IRefreshToken $refreshToken): void {
        $this->getTable()->insert(
            [
                'refresh_token' => $refreshToken->getRefreshToken(),
                'client_id' => $refreshToken->getClientId(),
                'user_id' => $refreshToken->getUserId(),
                'expires' => $refreshToken->getExpires(),
            ]
        );
    }

    /**
     * Remove refresh token
     */
    public function remove(string $refreshToken): void {
        $this->getTable()->where(['refresh_token' => $refreshToken])->delete();
    }

    /**
     * Get valid refresh token
     */
    public function getValidRefreshToken(string $refreshToken): ?IRefreshToken {
        $row = $this->getTable()
            ->where(['refresh_token' => $refreshToken])
            ->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
            ->fetch();

        if (!$row) {
            return null;
        }

        return new RefreshToken(
            $row['refresh_token'],
            new DateTime($row['expires']),
            $row['client_id'],
            $row['user_id']
        );
    }
}
