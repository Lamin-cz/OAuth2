<?php

namespace Drahak\OAuth2;

/**
 * IKeyGenerator
 * @package Drahak\OAuth2
 * @author Drahomír Hanák
 */
interface IKeyGenerator {

    /**
     * Generate random token
     */
    public function generate(int $length = 40): string;
}
