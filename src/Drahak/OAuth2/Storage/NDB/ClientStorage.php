<?php

namespace Drahak\OAuth2\Storage\NDB;

use Drahak\OAuth2\Storage\Clients\IClientStorage;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\Clients\Client;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * Nette database client storage
 * @package Drahak\OAuth2\Storage\Clients
 * @author Drahomír Hanák
 */
class ClientStorage implements IClientStorage {

    public function __construct(private readonly Explorer $context) {
    }

    /**
     * Get client table selection
     */
    protected function getTable(): Selection {
        return $this->context->table('oauth_client');
    }

    /**
     * Find client by ID and/or secret key
     */
    public function getClient(int|string $clientId, string $clientSecret = null): ?IClient {
        if (!$clientId) {
            return null;
        }

        $selection = $this->getTable()->where(['client_id' => $clientId]);
        if ($clientSecret) {
            $selection->where(['secret' => $clientSecret]);
        }
        $data = $selection->fetch();
        if (!$data) {
            return null;
        }
        return new Client($data['client_id'], $data['secret'], $data['redirect_url']);
    }

    /**
     * Can client use given grant type
     */
    public function canUseGrantType(string $clientId, string $grantType): bool {
        $result = $this->context->getConnection()->query(
            '
            SELECT g.name
            FROM oauth_client_grant AS cg
            RIGHT JOIN oauth_grant AS g ON cg.grant_id = cg.grant_id AND g.name = ?
            WHERE cg.client_id = ?
        ',
            $grantType,
            $clientId
        );
        return (bool)$result->fetch();
    }
}
