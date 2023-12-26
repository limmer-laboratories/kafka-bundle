<?php

namespace LimLabs\KafkaBundle\Tests\Unit\Kafka;

use LimLabs\KafkaBundle\Kafka\KafkaTopic;
use PHPUnit\Framework\TestCase;
use RdKafka\ProducerTopic;

class KafkaTopicTest extends TestCase
{
    public function testProduce(): void
    {
        $producerTopic = $this->createMock(ProducerTopic::class);
        $producerTopic->expects($this->once())
            ->method('produce')
            ->with(RD_KAFKA_PARTITION_UA, 0, 'test_message');

        $kafkaTopic = new KafkaTopic($producerTopic);
        $kafkaTopic->produce('test_message');
    }
}