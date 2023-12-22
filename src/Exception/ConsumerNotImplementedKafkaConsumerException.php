<?php

namespace LimLabs\KafkaBundle\Exception;

use Exception;
use Throwable;

class ConsumerNotImplementedKafkaConsumerException extends Exception
{
    public function __construct(
        string $message = 'Every as consumer tagged service has to implement the KafkaConsumer interface',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}