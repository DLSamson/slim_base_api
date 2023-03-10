<?php

namespace Api\Core\Services;

use Api\Core\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class Authorization {
    public const SUCCESS = 1;
    public const FAIL = 2;
    public const NULL = 3;

    public static function Auth($authHash = '') {
        if ($authHash == '') return self::NULL;

        [$email, $password] = explode(':', base64_decode((string) $authHash));

        $user = User::where(['email' => $email])->first();
        if(!$user) return self::FAIL;

        $result = password_verify($password, $user->passwordHash);
        return $result
            ? self::SUCCESS
            : self::FAIL;
    }

    public static function AuthAllowNull(Request $request, RequestHandler $requestHandler) : ResponseInterface {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code === self::FAIL) {
            $response = new Response();
            $response->getBody()->write('Неверные авторизационные данные');
            return $response->withStatus(401);
        }

        $response = $requestHandler->handle($request);
        return $response;
    }
    public static function AuthStrict(Request $request, RequestHandler $requestHandler) : ResponseInterface {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code !== self::SUCCESS) {
            $response = new Response();
            $response->getBody()->write('Неверные авторизационные данные');
            return $response->withStatus(401);
        }

        $response = $requestHandler->handle($request);
        return $response;
    }
}