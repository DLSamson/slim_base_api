<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class EchoController {
    public function __construct(LoggerInterface $log) {
        $this->log = $log;
    }

    public function echo(Request $request, Response $response, $args = []) {
        $this->log->error('Error happened!');
        $response->getBody()->write(json_encode(['Hello' => 'world!']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}