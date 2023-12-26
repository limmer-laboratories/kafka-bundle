<?php

namespace LimLabs\KafkaBundle\Tests\Unit\Kafka;

use LimLabs\KafkaBundle\Kafka\KafkaClient;
use LimLabs\KafkaBundle\Kafka\KafkaProducer;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use RdKafka\Conf;

class KafkaClientTest extends TestCase
{
    public function testCreateKafkaClient(): void
    {
        $brokers = 'test:9092';

        $config = new Conf();
        $config->set('log_level', 0);

        $kafkaClient = new KafkaClient($brokers, $config);

        $this->assertInstanceOf(KafkaProducer::class, $kafkaClient->getProducer());
        $this->assertSame($brokers, $kafkaClient->getBrokers());
        $this->assertSame($config, $kafkaClient->getConfig());
    }
}