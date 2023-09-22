<?php

namespace LambdaDigamma\MMEvents;

interface Generator
{
    /**
     * Generate an URL to add event to calendar.
     */
    public function generate(Link $link): string;
}
