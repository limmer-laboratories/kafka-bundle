<?php

namespace LimLabs\KafkaBundle\Exception;

use Exception;
use Throwable;

class CouldNotCreateTopic extends Exception
{
    public function __construct(
        string $message = "Could not create kafka topic",
        int $code = 0,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}