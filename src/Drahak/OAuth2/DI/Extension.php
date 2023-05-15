<?php

namespace Drahak\OAuth2\DI;

use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Definition;

/**
 * OAuth2 compiler extension
 * @package Drahak\OAuth2\DI
 * @author Drahomír Hanák
 */
class Extension extends CompilerExtension {
    protected array $storages = [
        'ndb' => [
            'accessTokenStorage' => 'Drahak\OAuth2\Storage\NDB\AccessTokenStorage',
            'authorizationCodeStorage' => 'Drahak\OAuth2\Storage\NDB\AuthorizationCodeStorage',
            'clientStorage' => 'Drahak\OAuth2\Storage\NDB\ClientStorage',
            'refreshTokenStorage' => 'Drahak\OAuth2\Storage\NDB\RefreshTokenStorage',
        ],
        'dibi' => [
            'accessTokenStorage' => 'Drahak\OAuth2\Storage\Dibi\AccessTokenStorage',
            'authorizationCodeStorage' => 'Drahak\OAuth2\Storage\Dibi\AuthorizationCodeStorage',
            'clientStorage' => 'Drahak\OAuth2\Storage\Dibi\ClientStorage',
            'refreshTokenStorage' => 'Drahak\OAuth2\Storage\Dibi\RefreshTokenStorage',
        ],
    ];

    /**
     * Default DI settings
     */
    protected array $defaults = [
        'accessTokenStorage' => null,
        'authorizationCodeStorage' => null,
        'clientStorage' => null,
        'refreshTokenStorage' => null,
        'accessTokenLifetime' => 3600, // 1 hour
        'refreshTokenLifetime' => 36000, // 10 hours
        'authorizationCodeLifetime' => 360, // 6 minutes
        'storage' => null,
    ];

    /**
     * Load DI configuration
     */
    public function loadConfiguration(): void {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        // Library common
        $container->addDefinition($this->prefix('keyGenerator'))
            ->setType('Drahak\OAuth2\KeyGenerator');

        $container->addDefinition($this->prefix('input'))
            ->setType('Drahak\OAuth2\Http\Input');

        // Grant types
        $container->addDefinition($this->prefix('authorizationCodeGrant'))
            ->setType('Drahak\OAuth2\Grant\AuthorizationCode');
        $container->addDefinition($this->prefix('refreshTokenGrant'))
            ->setType('Drahak\OAuth2\Grant\RefreshToken');
        $container->addDefinition($this->prefix('passwordGrant'))
            ->setType('Drahak\OAuth2\Grant\Password');
        $container->addDefinition($this->prefix('implicitGrant'))
            ->setType('Drahak\OAuth2\Grant\Implicit');
        $container->addDefinition($this->prefix('clientCredentialsGrant'))
            ->setType('Drahak\OAuth2\Grant\ClientCredentials');

        $container->addDefinition($this->prefix('grantContext'))
            ->setType('Drahak\OAuth2\Grant\GrantContext')
            ->addSetup('$service->addGrantType(?)', [$this->prefix('@authorizationCodeGrant')])
            ->addSetup('$service->addGrantType(?)', [$this->prefix('@refreshTokenGrant')])
            ->addSetup('$service->addGrantType(?)', [$this->prefix('@passwordGrant')])
            ->addSetup('$service->addGrantType(?)', [$this->prefix('@implicitGrant')])
            ->addSetup('$service->addGrantType(?)', [$this->prefix('@clientCredentialsGrant')]);

        // Tokens
        $container->addDefinition($this->prefix('accessToken'))
            ->setType('Drahak\OAuth2\Storage\AccessTokens\AccessTokenFacade')
            ->setArguments([$config['accessTokenLifetime']]);
        $container->addDefinition($this->prefix('refreshToken'))
            ->setType('Drahak\OAuth2\Storage\RefreshTokens\RefreshTokenFacade')
            ->setArguments([$config['refreshTokenLifetime']]);
        $container->addDefinition($this->prefix('authorizationCode'))
            ->setType('Drahak\OAuth2\Storage\AuthorizationCodes\AuthorizationCodeFacade')
            ->setArguments([$config['authorizationCodeLifetime']]);

        $container->addDefinition('tokenContext')
            ->setType('Drahak\OAuth2\Storage\TokenContext')
            ->addSetup('$service->addToken(?)', [$this->prefix('@accessToken')])
            ->addSetup('$service->addToken(?)', [$this->prefix('@refreshToken')])
            ->addSetup('$service->addToken(?)', [$this->prefix('@authorizationCode')]);

        // Nette database Storage
        if (strtoupper($config['storage']) == 'NDB' || (is_null($config['storage']) && $this->getByType(
                    $container,
                    'Nette\Database\Context'
                ))
        ) {
            $storageIndex = 'ndb';
        } elseif (strtoupper($config['storage']) == 'DIBI' || (is_null($config['storage']) && $this->getByType(
                    $container,
                    'DibiConnection'
                ))
        ) {
            $storageIndex = 'dibi';
        }

        $container->addDefinition($this->prefix('accessTokenStorage'))
            ->setType($config['accessTokenStorage'] ?: $this->storages[$storageIndex]['accessTokenStorage']);
        $container->addDefinition($this->prefix('refreshTokenStorage'))
            ->setType($config['refreshTokenStorage'] ?: $this->storages[$storageIndex]['refreshTokenStorage']);
        $container->addDefinition($this->prefix('authorizationCodeStorage'))
            ->setType(
                $config['authorizationCodeStorage'] ?: $this->storages[$storageIndex]['authorizationCodeStorage']
            );
        $container->addDefinition($this->prefix('clientStorage'))
            ->setType($config['clientStorage'] ?: $this->storages[$storageIndex]['clientStorage']);
    }

    private function getByType(ContainerBuilder $container, string $type): ?Definition {
        $definitions = $container->getDefinitions();
        foreach ($definitions as $definition) {
            if ($definition->class === $type) {
                return $definition;
            }
        }
        return null;
    }

    /**
     * Register OAuth2 extension
     * @param Configurator $configurator
     */
    public static function install(Configurator $configurator): void {
        $configurator->onCompile[] = function($configurator, $compiler) {
            $compiler->addExtension('oauth2', new Extension());
        };
    }

}
