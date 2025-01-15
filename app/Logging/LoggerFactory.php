<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerFactory
{
    public function createLogger(array $config)
    {
        $logger = new Logger('tastyigniter');

        if (isset($config['path'])) {
            $logger->pushHandler(new StreamHandler(
                $config['path'],
                $config['level'] ?? Logger::DEBUG
            ));
        }

        return $logger;
    }

    public function __invoke(array $config)
    {
        return $this->createLogger($config);
    }
}
