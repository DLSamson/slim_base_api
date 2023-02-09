<?php

namespace Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EchoController extends BaseController {

    public function echo(Request $request, Response $response, $args) {
        $this->log->info('Got request from: '. $request->getServerParams()['REMOTE_ADDR']);

        $response->getBody()->write($args['value'] ?: 'No value specified');

        return $response
            ->withStatus(200);
    }

    public function ping(Request $request, Response $response) {
        $response->getBody()->write('pong');

        return $response
            ->withStatus(200);
    }
}