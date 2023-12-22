<?php

namespace LimLabs\KafkaBundle\Service;

use RdKafka\Conf;
use RdKafka\Producer;

class KafkaProducer
{
    public function produce(string $message): void
    {
        $config = new Conf();
        $config->set('log_level', (string) LOG_DEBUG);
        $config->set('debug', 'all');

        $producer = new Producer($config);
        $producer->addBrokers("kafka:9092");

        $topic = $producer->newTopic('test');
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

        $producer->flush(100);
    }
}