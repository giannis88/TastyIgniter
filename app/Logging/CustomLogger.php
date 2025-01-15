<?php

namespace App\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Illuminate\Events\Dispatcher;

class CustomLogger implements LoggerInterface
{
    use LoggerTrait;

    private LoggerInterface $logger;
    private Dispatcher $dispatcher;

    public function __construct(LoggerInterface $logger, Dispatcher $dispatcher)
    {
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public static function createWithNullableDispatcher(LoggerInterface $logger, ?Dispatcher $dispatcher = null): self
    {
        return new self($logger, $dispatcher ?? new Dispatcher);
    }

    public function log($level, $message, array $context = []): void
    {
        if (is_object($message) && !$message instanceof \Stringable) {
            $message = serialize($message);
        }

        $this->logger->log($level, (string) $message, $context);
        $this->dispatcher->dispatch('tastyigniter.log', [$level, $message, $context]);
    }
}
