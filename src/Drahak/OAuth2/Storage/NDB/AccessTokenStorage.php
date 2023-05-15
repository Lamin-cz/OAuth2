<?php

namespace Drahak\OAuth2\Storage\NDB;

use DateTime;
use Drahak\OAuth2\Exceptions\InvalidScopeException;
use Drahak\OAuth2\Storage\AccessTokens\AccessToken;
use Drahak\OAuth2\Storage\AccessTokens\IAccessTokenStorage;
use Drahak\OAuth2\Storage\AccessTokens\IAccessToken;
use Nette\Database\Explorer;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * AccessTokenStorage
 * @package Drahak\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 */
class AccessTokenStorage implements IAccessTokenStorage {
    public function __construct(private readonly Explorer $context) {
    }

    /**
     * Get authorization code table
     */
    protected function getTable(): Selection {
        return $this->context->table('oauth_access_token');
    }

    /**
     * Get scope table
     */
    protected function getScopeTable(): Selection {
        return $this->context->table('oauth_access_token_scope');
    }

    /******************** IAccessTokenStorage ********************/

    /**
     * Store access token
     * @throws InvalidScopeException
     */
    public function store(IAccessToken $accessToken): void {
        $this->context->beginTransaction();
        $this->getTable()->insert(
            [
                'access_token' => $accessToken->getAccessToken(),
                'client_id' => $accessToken->getClientId(),
                'user_id' => $accessToken->getUserId(),
                'expires' => $accessToken->getExpires(),
            ]
        );

        try {
            foreach ($accessToken->getScope() as $scope) {
                $this->getScopeTable()->insert(
                    [
                        'access_token' => $accessToken->getAccessToken(),
                        'scope_name' => $scope,
                    ]
                );
            }
        } catch (\PDOException $e) {
            // MySQL's error 1452 - Cannot add or update a child row: a foreign key constraint fails
            if (in_array(1452, $e->errorInfo)) {
                throw new InvalidScopeException();
            }
            throw $e;
        }
        $this->context->commit();
    }

    /**
     * Remove access token
     */
    public function remove(string $accessToken): void {
        $this->getTable()->where(['access_token' => $accessToken])->delete();
    }

    /**
     * Get valid access token
     */
    public function getValidAccessToken(string $accessToken): ?IAccessToken {
        /** @var ActiveRow $row */
        $row = $this->getTable()
            ->where(['access_token' => $accessToken])
            ->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
            ->fetch();

        if (!$row) {
            return null;
        }

        $scopes = $this->getScopeTable()
            ->where(['access_token' => $accessToken])
            ->fetchPairs('scope_name');

        return new AccessToken(
            $row['access_token'],
            new DateTime($row['expires']),
            $row['client_id'],
            $row['user_id'],
            array_keys($scopes)
        );
    }
}
