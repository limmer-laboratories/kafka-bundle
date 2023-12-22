<?php

namespace LimLabs\KafkaBundle\Exception;

use Exception;
use Throwable;

class RequestedKafkaClientNonExisting extends Exception
{
    public function __construct(
        string $message = "The requested kafka client is not configured.",
        int $code = 0, ?Throwable
        $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}