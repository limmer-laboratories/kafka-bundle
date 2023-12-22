<?php

namespace LimLabs\KafkaBundle\Kafka;

use RdKafka\ProducerTopic;

class KafkaTopic
{
    public function __construct(
        private readonly ProducerTopic $producerTopic
    )
    {
    }

    public function produce(string $message): void
    {
        $this->producerTopic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
    }
}