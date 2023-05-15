<?php

namespace Drahak\OAuth2\Storage\NDB;

use DateTime;
use Drahak\OAuth2\Exceptions\InvalidScopeException;
use Drahak\OAuth2\Storage\AuthorizationCodes\AuthorizationCode;
use Drahak\OAuth2\Storage\AuthorizationCodes\IAuthorizationCodeStorage;
use Drahak\OAuth2\Storage\AuthorizationCodes\IAuthorizationCode;
use Nette\Database\Explorer;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * AuthorizationCode
 * @package Drahak\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
class AuthorizationCodeStorage implements IAuthorizationCodeStorage {

    public function __construct(private readonly Explorer $context) {
    }

    /**
     * Get authorization code table
     */
    protected function getTable(): Selection {
        return $this->context->table('oauth_authorization_code');
    }

    /**
     * Get scope table
     */
    protected function getScopeTable(): Selection {
        return $this->context->table('oauth_authorization_code_scope');
    }

    /******************** IAuthorizationCodeStorage ********************/

    /**
     * Store authorization code
     * @param IAuthorizationCode $authorizationCode
     * @throws InvalidScopeException
     */
    public function store(IAuthorizationCode $authorizationCode): void {
        $this->getTable()->insert(
            [
                'authorization_code' => $authorizationCode->getAccessToken(),
                'client_id' => $authorizationCode->getClientId(),
                'user_id' => $authorizationCode->getUserId(),
                'expires' => $authorizationCode->getExpires(),
            ]
        );

        $this->context->beginTransaction();
        try {
            foreach ($authorizationCode->getScope() as $scope) {
                $this->getScopeTable()->insert(
                    [
                        'authorization_code' => $authorizationCode->getAccessToken(),
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
     * Remove authorization code
     */
    public function remove(string $authorizationCode): void {
        $this->getTable()->where(['authorization_code' => $authorizationCode])->delete();
    }

    /**
     * Validate authorization code
     */
    public function getValidAuthorizationCode(string $authorizationCode): ?IAuthorizationCode {
        /** @var ActiveRow $row */
        $row = $this->getTable()
            ->where(['authorization_code' => $authorizationCode])
            ->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
            ->fetch();

        if (!$row) {
            return null;
        }

        $scopes = $this->getScopeTable()
            ->where(['authorization_code' => $authorizationCode])
            ->fetchPairs('scope_name');

        return new AuthorizationCode(
            $row['authorization_code'],
            new DateTime($row['expires']),
            $row['client_id'],
            $row['user_id'],
            array_keys($scopes)
        );
    }
}
