<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model {
    public $table = 'animals';
    public $fillable = [
        'weight', 'length', 'height',
        'gender', 'lifeStatus', 'chipperId',
    ];

    public function location() {
        return $this->hasOne(Location::class, 'id', 'chippingLocationId');
    }

    public function locations() {
        return $this->belongsToMany(
            Location::class,
            'animals_locations',
            'animal_id',
            'location_id'
        );
    }

    public function types() {
        return $this->belongsToMany(
            Type::class,
            'animals_types',
            'animal_id',
            'type_id'
        );
    }
}