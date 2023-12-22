<?php

namespace LimLabs\KafkaBundle\Kafka;

use LimLabs\KafkaBundle\Exception\CouldNotCreateTopic;
use RdKafka\Conf;
use RdKafka\Producer;

class KafkaProducer
{
    private readonly Producer $producer;

    public function __construct(string $brokers, Conf $config)
    {
        $this->producer = new Producer($config);
        $this->producer->addBrokers($brokers);
    }

    /**
     * @throws CouldNotCreateTopic
     */
    public function createTopic(string $name): KafkaTopic
    {
        $topic = $this->producer->newTopic($name);

        if ($topic == null) {
            throw new CouldNotCreateTopic();
        }

        return new KafkaTopic($topic);
    }

    public function flush(): void
    {
        $this->producer->flush(500); //TODO: Get from config
    }
}