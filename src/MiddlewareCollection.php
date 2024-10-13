<?php

namespace Fenrir\Framework;
use ArrayIterator;

class MiddlewareCollection extends ArrayIterator
{
    /**
     * @var class-string
     */
    public function __construct(string ...$middlewares) {
        parent::__construct($middlewares);
    }
}
