<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalLocation extends Model {
    protected $table = 'animals_locations';
    protected $fillable = [
        'animal_id', 'location_id'
    ];
}