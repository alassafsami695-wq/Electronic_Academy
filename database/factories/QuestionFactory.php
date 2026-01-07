<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'question' => $this->faker->sentence,
            'options' => ['Option 1', 'Option 2', 'Option 3', 'Option 4'],
            'answer' => 'Option 1',
            'type' => 'multiple_choice'
        ];
    }
}