<?php

namespace Api\Core\Services;
use Api\Core\Models\Animal;
use Api\Core\Models\AnimalLocation;
use Illuminate\Database\Eloquent\Collection;

class AnimalDataFormatter {
    /**
     * @param Animal $animal
     *
     * @return array
     */
    public static function prepareForRespone(Animal $animal) {
        return [
            'id' => $animal->id,
            'animalTypes' => $animal->types()
                ->get()->map(fn($el) => $el->id)->toArray(),
            'weight' => $animal->weight,
            'length' => $animal->length,
            'height' => $animal->height,
            'gender' => $animal->gender,
            'lifeStatus' => $animal->lifeStatus,
            'chippingDateTime' => DateFormatter::formatToISO8601($animal->chippingDateTime),
            'chipperId' => $animal->chipperId,
            'chippingLocationId' => $animal->chippingLocationId,
            'visitedLocations' => AnimalLocation::where(['animal_id' => $animal->id])
                ->get()->map(fn($el) => $el->id),
            'deathDateTime' => $animal->lifeStatus == 'DEAD'
                ? DateFormatter::formatToISO8601($animal->deathDateTime)
                : null,
        ];
    }

    /**
     * @param Collection $animals
     *
     * @return array
     */
    public static function prepareCollectionForRespone(Collection $animals) {
        return $animals->map(fn($animal) => [
            'id' => $animal->id,
            'animalTypes' => $animal->types()
                ->get()->map(fn($el) => $el->id),
            'weight' => $animal->weight,
            'length' => $animal->length,
            'height' => $animal->height,
            'gender' => $animal->gender,
            'lifeStatus' => $animal->lifeStatus,
            'chippingDateTime' => DateFormatter::formatToISO8601($animal->chippingDateTime),
            'chipperId' => $animal->chipperId,
            'chippingLocationId' => $animal->chippingLocationId,
            'visitedLocations' => AnimalLocation::where(['animal_id' => $animal->id])
                ->get()->map(fn($el) => $el->id),
            'deathDateTime' => $animal->lifeStatus == 'DEAD'
                ? DateFormatter::formatToISO8601($animal->deathDateTime)
                : null,
        ])->toArray();
    }
}