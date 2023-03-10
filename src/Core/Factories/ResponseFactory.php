<?php

namespace Api\Core\Factories;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class ResponseFactory {

    public static function BadRequest($errors = []) : ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode($errors));

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(400);
    }

    public static function Unauthorized($message = 'Not Authorized') : ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode($message));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }

    public static function Forbidden($message = 'Now Allowed') : ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode($message));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }

    public static function NotFound($message = 'Not Found') : ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode($message));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }

    public static function Conflict($message = 'Conflict') : ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode($message));

        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(409);
    }
}