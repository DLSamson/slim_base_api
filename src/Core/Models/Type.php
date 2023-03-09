<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model {
    public $table = 'types';
    public $fillable = [
        'type',
    ];

}