<?php

namespace LimLabs\KafkaBundle\Exception;

use Exception;
use Throwable;

class MissingSubscribedTopicsException extends Exception
{
    public function __construct(
        string $message = "Missing subscribed_topics configuration",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}