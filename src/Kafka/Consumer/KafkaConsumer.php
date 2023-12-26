<?php

namespace LimLabs\KafkaBundle\Kafka\Consumer;

use LimLabs\KafkaBundle\Enum\ConsumerResponse;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kafka.consumer')]
interface KafkaConsumer
{
    public function consume(string $message): ConsumerResponse;

    public function getConsumerConfiguration(): ConsumerConfiguration;
}