<?php

namespace LimLabs\KafkaBundle\Exception;

use Exception;
use Throwable;

class NoDefaultConfigurationExisting extends Exception
{
    public function __construct(
        string $message = "No default configuration has been provided",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}