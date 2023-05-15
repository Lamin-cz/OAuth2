<?php

namespace Drahak\OAuth2\Http;

use Nette\Http\IRequest;

/**
 * Input parser
 * @package Drahak\OAuth2\Http
 * @author Drahomír Hanák
 */
class Input implements IInput {
    private array $data = [];

    public function __construct(private readonly IRequest $request) {
    }

    /**
     * Get all parameters
     */
    public function getParameters(): array {
        if (!$this->data) {
            if ($this->request->getQuery()) {
                $this->data = $this->request->getQuery();
            } else {
                if ($this->request->getPost()) {
                    $this->data = $this->request->getPost();
                } else {
                    $this->data = $this->parseRequest(file_get_contents('php://input'));
                }
            }
        }
        return $this->data;
    }

    /**
     * Get single parameter by key
     */
    public function getParameter(string $name): null|int|string {
        $parameters = $this->getParameters();
        return $parameters[$name] ?? null;
    }

    /**
     * Get authorization token from header - Authorization: Bearer
     */
    public function getAuthorization(): ?string {
        $authorization = explode(' ', $this->request->getHeader('Authorization'));
        return $authorization[1] ?? null;
    }

    /**
     * Convert client request data to array or traversable
     */
    private function parseRequest(string $data): array {
        $result = [];
        parse_str($data, $result);
        return $result;
    }

}
