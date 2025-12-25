<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'course_id' => \App\Models\Course::factory(),
            'order' => $this->faker->randomDigitNotNull, // حل مشكلة الـ order
            'content' => $this->faker->paragraph,
        ];
    }
}
