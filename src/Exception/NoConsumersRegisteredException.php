<?php

namespace LimLabs\KafkaBundle\Exception;

use Exception;
use Throwable;

class NoConsumersRegisteredException extends Exception
{
    public function __construct(
        string $message = 'No consumers are registered. Did you forget to implement the interface?',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}