<?php

namespace Drahak\OAuth2\Grant;

use Drahak\OAuth2\Http\IInput;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\Clients\IClientStorage;
use Drahak\OAuth2\Storage\TokenContext;
use Drahak\OAuth2\Exceptions\UnauthorizedClientException;
use Nette\Security\User;

/**
 * GrantType
 * @package Drahak\OAuth2\Grant
 * @author Drahomír Hanák
 *
 * @property-read string $identifier
 */
abstract class GrantType implements IGrant {
    const SCOPE_KEY = 'scope';
    const CLIENT_ID_KEY = 'client_id';
    const CLIENT_SECRET_KEY = 'client_secret';
    const GRANT_TYPE_KEY = 'grant_type';

    private ?IClient $client = null;

    /**
     * @param IInput $input
     * @param TokenContext $token
     * @param IClientStorage $clientStorage
     * @param User $user
     */
    public function __construct(
        protected readonly IInput         $input,
        protected readonly TokenContext   $token,
        protected readonly IClientStorage $clientStorage,
        protected readonly User           $user
    ) {
    }

    /**
     * Get client
     */
    protected function getClient(): IClient {
        if (!$this->client) {
            $clientId = $this->input->getParameter(self::CLIENT_ID_KEY);
            $clientSecret = $this->input->getParameter(self::CLIENT_SECRET_KEY);
            $this->client = $this->clientStorage->getClient($clientId, $clientSecret);
        }
        return $this->client;
    }

    /**
     * Get scope as array - allowed separators: ',' AND ' '
     */
    protected function getScope(): array {
        $scope = $this->input->getParameter(self::SCOPE_KEY);
        return !is_array($scope) ?
            array_filter(explode(',', str_replace(' ', ',', $scope))) :
            $scope;
    }

    /****************** IGrant interface ******************/

    /**
     * Get access token
     * @throws UnauthorizedClientException
     * @throws InvalidGrantTypeException
     */
    final public function getAccessToken(): array {
        if (!$this->getClient()) {
            throw new UnauthorizedClientException('Client is not found');
        }

        $this->verifyGrantType();
        $this->verifyRequest();
        return $this->generateAccessToken();
    }

    /****************** Access token template methods ******************/

    /**
     * Verify grant type
     * @throws UnauthorizedClientException
     * @throws InvalidGrantTypeException
     */
    protected function verifyGrantType(): void {
        $grantType = $this->input->getParameter(self::GRANT_TYPE_KEY);
        if (!$grantType) {
            throw new InvalidGrantTypeException();
        }

        if (!$this->clientStorage->canUseGrantType($this->getClient()->getId(), $grantType)) {
            throw new UnauthorizedClientException();
        }
    }

    /**
     * Verify request
     */
    abstract protected function verifyRequest(): void;

    /**
     * Generate access token
     */
    abstract protected function generateAccessToken(): array;

}
