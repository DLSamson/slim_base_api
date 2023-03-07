<?php

namespace Api\Core\Services;

use Api\Core\Models\User;

class Authorization {
    public const SUCCESS = 1;
    public const FAIL = 2;
    public const NULL = 3;

    public static function Auth($authHash = '') {
        if ($authHash == '') return self::NULL;

        [$email, $password] = explode(':', base64_decode((string) $authHash));

        $user = User::where([
            'email'        => $email,
            'passwordHash' => User::HashPassword($password),
        ])->first();

        return $user
            ? self::SUCCESS
            : self::FAIL;
    }
}