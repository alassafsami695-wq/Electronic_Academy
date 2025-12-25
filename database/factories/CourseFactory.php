<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   
    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'price' => 1000,
            'teacher_id' => \App\Models\User::factory(),
            'path_id' => \App\Models\Path::factory(), 
        ];
    }
}
