<?php

namespace Api\Core\Models;
use Illuminate\Database\Eloquent\Model;

class User extends Model{
    public $table = 'users';
    public $fillable = [
        'firstName', 'lastName', 'email', 'passwordHash'
    ];

    public static function Auth($authHash = '') {
        if(!$authHash) return false;

        [$email, $password] = explode(':', base64_decode((string) $authHash));
        return (bool) self::where([
            'email'        => $email,
            'passwordHash' => self::HashPassword($password),
        ])->get();
    }

    public static function HashPassword(string $password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}