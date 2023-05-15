<?php

namespace Drahak\OAuth2\Storage\Dibi;

use Drahak\OAuth2\Exceptions\InvalidScopeException;
use Drahak\OAuth2\Storage\AuthorizationCodes\AuthorizationCode;
use Drahak\OAuth2\Storage\AuthorizationCodes\IAuthorizationCodeStorage;
use Drahak\OAuth2\Storage\AuthorizationCodes\IAuthorizationCode;
use Nette\Database\Table\ActiveRow;

/**
 * AuthorizationCode
 * @package Drahak\OAuth2\Storage\AuthorizationCodes
 * @author Martin Malek
 */
class AuthorizationCodeStorage implements IAuthorizationCodeStorage {
    public function __construct(private readonly \DibiConnection $context) {
        // todo Dibi
    }

    /**
     * Get authorization code table
     */
    protected function getTable(): string {
        return 'oauth_authorization_code';
    }

    /**
     * Get scope table
     */
    protected function getScopeTable(): string {
        return 'oauth_authorization_code_scope';
    }

    /******************** IAuthorizationCodeStorage ********************/

    /**
     * Store authorization code
     * @throws InvalidScopeException
     */
    public function store(IAuthorizationCode $authorizationCode): void {
        $this->context->insert($this->getTable(), [
            'authorization_code' => $authorizationCode->getAccessToken(),
            'client_id' => $authorizationCode->getClientId(),
            'user_id' => $authorizationCode->getUserId(),
            'expires_at' => $authorizationCode->getExpires(),
        ])->execute();

        $this->context->begin();
        try {
            foreach ($authorizationCode->getScope() as $scope) {
                $this->context->insert($this->getScopeTable(), [
                    'authorization_code' => $authorizationCode->getAccessToken(),
                    'scope_name' => $scope,
                ])->execute();
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
        $this->context->delete($this->getTable())->where('authorization_code = %s', $authorizationCode)->execute();
    }

    /**
     * Validate authorization code
     */
    public function getValidAuthorizationCode(string $authorizationCode): ?IAuthorizationCode {
        /** @var ActiveRow $row */
        $row = $this->context->select('*')->from($this->getTable())
            ->where('authorization_code = %s', $authorizationCode)
            ->where('TIMEDIFF(expires_at, NOW()) >= 0')
            ->fetch();

        if (!$row) {
            return null;
        }

        $scopes = $this->context->select('*')->from($this->getScopeTable())
            ->where('authorization_code = %s', $authorizationCode)
            ->fetchPairs('scope_name');

        return new AuthorizationCode(
            $row['authorization_code'],
            new \DateTime($row['expires_at']),
            $row['client_id'],
            $row['user_id'],
            array_keys($scopes)
        );
    }
}
