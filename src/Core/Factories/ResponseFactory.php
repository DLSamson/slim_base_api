<?php

namespace Api\Core\Factories;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class ResponseFactory {
    public static function Success($data = null) {
        $response = new Response();
        if($data !== null) {
            $response->getBody()->write(json_encode($data));
            $response = $response->withHeader('Content-Type', 'application/json');
        }
        return $response
            ->withStatus(200);
    }

    public static function Created($data = null) {
        $response = new Response();
        if($data !== null) {
            $response->getBody()->write(json_encode($data));
            $response = $response->withHeader('Content-Type', 'application/json');
        }
        return $response
            ->withStatus(201);
    }

    public static function BadRequest($errors = []) : ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode($errors));

        return $response
            ->withHeader('Content-Type', 'application/json')
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
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(409);
    }

    public static function InternalServerError() {
        $response = new Response();
        return $response
            ->withStatus(500);
    }
}