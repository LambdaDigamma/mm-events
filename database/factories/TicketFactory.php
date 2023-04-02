<?php

namespace LambdaDigamma\MMEvents\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LambdaDigamma\MMEvents\Models\Event;
use LambdaDigamma\MMEvents\Models\Ticket;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
        ];
    }

    public function published()
    {
        return $this->state(fn () => [
            'published_at' => now(),
        ]);
    }

    public function notPublished()
    {
        return $this->state(fn () => [
            'published_at' => null,
        ]);
    }

    public function archived()
    {
        return $this->state(fn () => [
            'archived_at' => now()
        ]);
    }

    public function notArchived()
    {
        return $this->state(fn () => [
            'archived_at' => null
        ]);
    }

}
