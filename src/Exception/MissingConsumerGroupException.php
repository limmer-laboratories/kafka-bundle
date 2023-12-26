<?php

namespace LimLabs\KafkaBundle\Exception;

use Exception;
use Throwable;

class MissingConsumerGroupException extends Exception
{
    public function __construct(
        string $message = "Missing consumer_group configuration",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}