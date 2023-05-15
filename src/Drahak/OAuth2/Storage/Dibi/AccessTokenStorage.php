<?php

namespace Drahak\OAuth2\Storage\Dibi;

use DateTime;
use Drahak\OAuth2\Exceptions\InvalidScopeException;
use Drahak\OAuth2\Storage\AccessTokens\AccessToken;
use Drahak\OAuth2\Storage\AccessTokens\IAccessTokenStorage;
use Drahak\OAuth2\Storage\AccessTokens\IAccessToken;
use Nette\Database\Table\ActiveRow;
use PDOException;

/**
 * AccessTokenStorage
 * @package Drahak\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 */
class AccessTokenStorage implements IAccessTokenStorage {

    public function __construct(private readonly Dibi\Connection $context) {
        //TODO: Dibi
    }

    /**
     * Get authorization code table
     */
    protected function getTable(): string {
        return 'oauth_access_token';
    }

    /**
     * Get scope table
     */
    protected function getScopeTable(): string {
        return 'oauth_access_token_scope';
    }

    /******************** IAccessTokenStorage ********************/

    /**
     * Store access token
     * @param IAccessToken $accessToken
     * @throws InvalidScopeException
     */
    public function store(IAccessToken $accessToken): void {
        $this->context->begin();
        $this->context->insert($this->getTable(), [
            'access_token' => $accessToken->getAccessToken(),
            'client_id' => $accessToken->getClientId(),
            'user_id' => $accessToken->getUserId(),
            'expires_at' => $accessToken->getExpires(),
        ])->execute();

        try {
            foreach ($accessToken->getScope() as $scope) {
                $this->context->insert($this->getScopeTable(), [
                    'access_token' => $accessToken->getAccessToken(),
                    'scope_name' => $scope,
                ])->execute();
            }
        } catch (PDOException $e) {
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
     * @param string $accessToken
     */
    public function remove(string $accessToken): void {
    }

    /**
     * Get valid access token
     */
    public function getValidAccessToken(string $accessToken): ?IAccessToken {
        /** @var ActiveRow $row */
        $row = $this->context->select('*')->from($this->getTable())
            ->where('access_token = %s', $accessToken)
            ->where('TIMEDIFF(expires_at, NOW()) >= 0')
            ->fetch();

        if (!$row) {
            return null;
        }

        $scopes = $this->context->select('*')->from($this->getScopeTable())
            ->where('access_token = %s', $accessToken)
            ->fetchPairs('scope_name');

        return new AccessToken(
            $row['access_token'],
            new DateTime($row['expires_at']),
            $row['client_id'],
            $row['user_id'],
            array_keys($scopes)
        );
    }
}
