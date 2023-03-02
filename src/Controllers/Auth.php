<?php

namespace Api\Controllers;

use Api\Core\Http\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Api\Core\Models\User;

class Auth extends BaseController {
    public function register(Request $request, Response $response) {
        $json = $request->getBody()->getContents();
        $data = json_decode($json);
        $this->log->info($json);
        return $response->withStatus(201);
    }
}