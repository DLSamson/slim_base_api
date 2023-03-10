<?php

namespace Api\Core\Services;

use Api\Core\Factories\ResponseFactory;
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

        $authHash = str_replace('Basic ', '', $authHash);
        [$email, $password] = explode(':', base64_decode($authHash));

        $user = User::where(['email' => $email])->first();
        if(!$user) return self::FAIL;

        $result = password_verify($password, $user->passwordHash);
        return $result
            ? self::SUCCESS
            : self::FAIL;
    }

    /**
     * @param   string  $authHash
     *
     * @return false|string
     */
    public static function getAuthenticatedEmail($authHash = '') {
        $authCode = self::Auth($authHash);
        if($authCode !== self::SUCCESS) return false;

        $authHash = str_replace('Basic ', '', $authHash);
        return explode(':', base64_decode($authHash))[0];
    }

    public static function AuthAllowNull(Request $request, RequestHandler $requestHandler) : ResponseInterface {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code === self::FAIL)
            return ResponseFactory::Unauthorized();

        return $requestHandler->handle($request);
    }
    public static function AuthStrict(Request $request, RequestHandler $requestHandler) : ResponseInterface {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code !== self::SUCCESS)
            return ResponseFactory::Unauthorized();

        return $requestHandler->handle($request);
    }

    public static function AuthNotAllowed(Request $request, RequestHandler $requestHandler) : ResponseInterface {
        $auth_code = self::Auth($request->getHeaderLine('Authorization'));
        if ($auth_code === self::SUCCESS)
            return ResponseFactory::Forbidden();

        return $requestHandler->handle($request);
    }
}