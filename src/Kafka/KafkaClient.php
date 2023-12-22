<?php

namespace LimLabs\KafkaBundle\Kafka;

use RdKafka\Conf;

class KafkaClient
{
    private readonly KafkaProducer $producer;

   public function __construct(
       private readonly string $brokers,
       private readonly Conf $config,
   )
   {
       $this->producer = new KafkaProducer($this->brokers, $this->config);
   }

    public function getProducer(): KafkaProducer
    {
        return $this->producer;
    }

    public function getBrokers(): string
    {
        return $this->brokers;
    }
}