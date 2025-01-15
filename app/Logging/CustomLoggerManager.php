<?php

namespace App\Logging;

use Illuminate\Log\LogManager;
use Illuminate\Events\Dispatcher;
use Psr\Log\LoggerInterface;

class CustomLoggerManager extends LogManager
{
    protected function createLogger(LoggerInterface $logger): LoggerInterface
    {
        $dispatcher = $this->app->make(Dispatcher::class);
        return new CustomLogger($logger, $dispatcher);
    }
}
