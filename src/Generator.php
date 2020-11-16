<?php

namespace LambdaDigamma\MMEvents;

interface Generator
{
    /**
     * Generate an URL to add event to calendar.
     * @param \LambdaDigamma\MMEvents\Link $link
     * @return string
     */
    public function generate(Link $link): string;
}
