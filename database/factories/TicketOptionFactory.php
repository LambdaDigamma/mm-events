<?php

namespace LambdaDigamma\MMEvents\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LambdaDigamma\MMEvents\Models\TicketOption;

class TicketOptionFactory extends Factory
{
    protected $model = TicketOption::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'price' => $this->faker->numberBetween(10, 40),
        ];
    }

}
