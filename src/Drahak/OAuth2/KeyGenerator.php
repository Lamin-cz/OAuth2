<?php

namespace Drahak\OAuth2;

/**
 * KeyGenerator
 * @package Drahak\OAuth2
 * @author Drahomír Hanák
 */
class KeyGenerator implements IKeyGenerator {

    /** Key generator algorithm */
    private const ALGORITHM = 'sha256';

    /**
     * Generate random token
     */
    public function generate(int $length = 40): string {
        $bytes = openssl_random_pseudo_bytes($length);
        return hash(self::ALGORITHM, $bytes);
    }
}
