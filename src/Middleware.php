<?php

namespace Fenrir\Framework;

interface Middleware
{
    public function execute(callable $next);
}
