<?php

namespace Api\Core\Models;
use Illuminate\Database\Eloquent\Model;

class Location extends Model {
    public $table = 'locations';
    public $fillable = [
        'latitude', 'longitude',
    ];
}