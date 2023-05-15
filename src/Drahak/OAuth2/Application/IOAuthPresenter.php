<?php

namespace Drahak\OAuth2\Application;

use Nette\Application\IPresenter;

/**
 * OAuth2 authorization server presenter
 * @package Drahak\OAuth2\Application
 * @author Drahomír Hanák
 */
interface IOAuthPresenter extends IPresenter {

    /**
     * Issue an authorization code
     */
    public function issueAuthorizationCode(string $responseType, string $redirectUrl, ?string $scope = null): void;

    /**
     * Issue an access token
     */
    public function issueAccessToken(): void;

}
