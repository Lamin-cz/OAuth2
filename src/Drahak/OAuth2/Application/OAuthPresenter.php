<?php

namespace Drahak\OAuth2\Application;

use Drahak\OAuth2\Grant\GrantContext;
use Drahak\OAuth2\Grant\GrantType;
use Drahak\OAuth2\Exceptions\InvalidGrantException;
use Drahak\OAuth2\Exceptions\InvalidStateException;
use Drahak\OAuth2\Exceptions\OAuthException;
use Drahak\OAuth2\Grant\InvalidGrantTypeException;
use Drahak\OAuth2\Storage\AuthorizationCodes\AuthorizationCodeFacade;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\Clients\IClientStorage;
use Drahak\OAuth2\Storage\Exceptions\TokenException;
use Drahak\OAuth2\Storage\Exceptions\InvalidAuthorizationCodeException;
use Drahak\OAuth2\Exceptions\UnauthorizedClientException;
use Drahak\OAuth2\Exceptions\UnsupportedResponseTypeException;
use JetBrains\PhpStorm\NoReturn;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Http\Url;
use Traversable;

/**
 * OauthPresenter
 * @package Drahak\OAuth2\Application
 * @author Drahomír Hanák
 */
class OAuthPresenter extends Presenter implements IOAuthPresenter {
    private GrantContext $grantContext;
    protected AuthorizationCodeFacade $authorizationCode;
    protected IClientStorage $clientStorage;
    protected ?IClient $client;

    /**
     * Inject grant strategy context
     */
    public function injectGrant(GrantContext $grantContext): void {
        $this->grantContext = $grantContext;
    }

    /**
     * Inject token manager - authorization code
     */
    public function injectAuthorizationCode(AuthorizationCodeFacade $authorizationCode): void {
        $this->authorizationCode = $authorizationCode;
    }

    /**
     * Injet client storage
     */
    public function injectClientStorage(IClientStorage $clientStorage): void {
        $this->clientStorage = $clientStorage;
    }

    /**
     * On presenter startup
     */
    protected function startup(): void {
        parent::startup();
        $this->client = $this->clientStorage->getClient(
            $this->getParameter(GrantType::CLIENT_ID_KEY),
            $this->getParameter(GrantType::CLIENT_SECRET_KEY)
        );
    }

    /**
     * Get grant type
     * @throws UnsupportedResponseTypeException
     */
    public function getGrantType(): GrantType {
        $request = $this->getHttpRequest();
        $grantType = $request->getPost(GrantType::GRANT_TYPE_KEY);
        try {
            return $this->grantContext->getGrantType($grantType);
        } catch (InvalidStateException $e) {
            throw new UnsupportedResponseTypeException('Trying to use unknown grant type ' . $grantType, $e);
        }
    }

    /**
     * Provide OAuth2 error response (redirect or at least JSON)
     */
    public function oauthError(OAuthException $exception): void {
        $error = [
            'error' => $exception->getKey(),
            'error_description' => $exception->getMessage(),
        ];
        $this->oauthResponse($error, $this->getParameter('redirect_uri'), $exception->getCode());
    }

    /**
     * Send OAuth response
     */
    #[NoReturn] public function oauthResponse(
        Traversable|array $data,
        ?string           $redirectUrl = null,
        int               $code = IResponse::S200_OK
    ): void {
        if ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        }

        // Redirect, if there is URL
        if ($redirectUrl !== null) {
            $url = new Url($redirectUrl);
            if ($this->getParameter('response_type') == 'token') {
                $url->setFragment(http_build_query($data));
            } else {
                $url->appendQuery($data);
            }
            $this->redirectUrl($url);
        }

        // else send JSON response
        foreach ($data as $key => $value) {
            $this->payload->$key = $value;
        }
        $this->getHttpResponse()->setCode($code);
        $this->sendResponse(new JsonResponse($this->payload));
    }

    public function issueAuthorizationCode(
        string $responseType,
        string $redirectUrl,
        string $scope = null,
        array  $state = null
    ): void {
        try {
            if ($responseType !== 'code') {
                throw new UnsupportedResponseTypeException();
            }
            if (!$this->client->getId()) {
                throw new UnauthorizedClientException();
            }

            $scope = array_filter(explode(',', str_replace(' ', ',', $scope)));
            $code = $this->authorizationCode->create($this->client, $this->user->getId(), $scope);
            $data = [
                'code' => $code->getAccessToken(),
            ];
            if (!empty($state)) {
                $data['state'] = $state;
            }
            $this->oauthResponse($data, $redirectUrl);
        } catch (OAuthException $e) {
            $this->oauthError($e);
        } catch (TokenException) {
            $this->oauthError(new InvalidGrantException());
        }
    }

    /**
     * Issue access token to client
     * @throws InvalidAuthorizationCodeException
     * @throws InvalidStateException
     * @throws InvalidGrantTypeException
     */
    public function issueAccessToken(string $grantType = null, string $redirectUrl = null): void {
        try {
            if ($grantType !== null) {
                $grantType = $this->grantContext->getGrantType($grantType);
            } else {
                $grantType = $this->getGrantType();
            }

            $response = $grantType->getAccessToken();
            $this->oauthResponse($response, $redirectUrl);
        } catch (OAuthException $e) {
            $this->oauthError($e);
        } catch (TokenException) {
            $this->oauthError(new InvalidGrantException());
        }
    }

}
