<?php

namespace LambdaDigamma\MMEvents;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LambdaDigamma\MMEvents\MMEvents
 */
class MMEventsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mm-events';
    }
}
