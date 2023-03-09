<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model {
    public $table = 'animals';
    public $fillable = [
        'weight', 'length', 'height',
        'gender', 'lifeStatus', 'chipperId',
    ];

}