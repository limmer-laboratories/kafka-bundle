<?php

namespace LimLabs\KafkaBundle\Enum;

enum ConsumerResponse
{
    case SUCCESS;
    case ERROR_DROP;
    case ERROR_REQUEUE;
}