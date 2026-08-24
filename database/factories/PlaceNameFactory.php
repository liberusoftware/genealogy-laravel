<?php

namespace Database\Factories;

use App\Models\PlaceName;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaceNameFactory extends Factory
{
    protected $model = PlaceName::class;

    public function definition()
    {
        return [
            'name' => $this->faker->city,
            'is_default' => true,
        ];
    }
}
