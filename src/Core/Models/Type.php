<?php

namespace Api\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model {
    public $table = 'types';
    public $fillable = [
        'type',
    ];

    public function animals() {
        return $this->belongsToMany(
            Animal::class,
            'animals_types',
            'type_id',
            'animal_id'
        );
    }
}