<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder(User::pluck('id')),
            'balance' => $this->faker->randomFloat(2, 0, 1000),
            'currency' => $this->faker->randomElement(['BRL', 'USD', 'EUR']),
            'description' => $this->faker->sentence,
        ];
    }
}
