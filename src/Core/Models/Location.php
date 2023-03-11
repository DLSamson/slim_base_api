<?php

namespace Api\Core\Models;
use Illuminate\Database\Eloquent\Model;

class Location extends Model {
    public $table = 'locations';
    public $fillable = [
        'latitude', 'longitude',
    ];

    public function animal() {
        $this->hasOne(Animal::class, 'chippingLocationId', 'id');
    }

    public function animals() {
        return $this->belongsToMany(
            Animal::class,
            'animals_locations',
            'location_id',
            'animal_id',
        );
    }
}