<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    public $table = 'users';
    public $fillable = [
        'firstName', 'lastName', 'email', 'passwordHash',
    ];

    public static function HashPassword($password) {
        return password_hash((string) $password, PASSWORD_DEFAULT);
    }
}