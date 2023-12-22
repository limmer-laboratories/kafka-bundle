<?php

namespace LimLabs\KafkaBundle\Kafka\Consumer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('kafka.consumer')]
interface KafkaConsumer
{
    public function consume(string $message): void;

    public function getConsumerConfiguration(): ConsumerConfiguration;
}