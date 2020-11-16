<?php

namespace LambdaDigamma\MMEvents\Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use LambdaDigamma\MMEvents\Event;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->realText(200),
            'url' => $this->faker->url,
            'image_path' => $this->faker->imageUrl(640, 480),
            'is_published' => $this->faker->boolean(85),
            'extras' => collect(['locationName' => $this->faker->word()]),
        ];
    }

    public function activeStartEnd() 
    {
        return $this
            ->state(fn () => [
                'start_date' => Carbon::now()->subRealMinutes($this->faker->numberBetween(1, 29))->toDateTimeString(),
                'end_date' => Carbon::now()->addRealMinutes($this->faker->numberBetween(29, 180))->toDateTimeString(),
            ]);
    }

    public function activeStart()
    {
        return $this
            ->state(fn () => [
                'start_date' => Carbon::now()->subRealMinutes($this->faker->numberBetween(1, 29))->toDateTimeString(),
            ]);
    }

    public function upcomingStart()
    {
        return $this
            ->state(fn () => [
                'start_date' => Carbon::now()->addRealMinutes($this->faker->numberBetween(1, 24) * 60)->toDateTimeString(),
            ]);
    }

    public function upcomingToday() 
    {
        return $this
            ->state(fn () => [
                'start_date' => $this->faker->dateTimeBetween(Carbon::now()->toDateTimeString(), Carbon::now()->setHours(23)->setMinutes(59)->setSeconds(0)->toDateTimeString())
            ]);
    }

    // public function withHeaderMedia()
    // {
    //     return $this->afterCreating(function (Event $event) {
    //         $event
    //             ->addMediaFromUrl($this->faker->imageUrl(640, 480))
    //             ->toMediaCollection('header');
    //     });
    // }

    public function published() 
    {
        return $this->state(fn () => [
            'is_published' => true,
            'scheduled_at' => null,
        ]);
    }

    public function notPublished()
    {
        return $this->state(fn () => [
            'is_published' => false,
            'scheduled_at' => $this->faker->dateTimeInInterval('tomorrow', '+ 10 days')
        ]);
    }

}
