<?php

namespace LimLabs\KafkaBundle\Tests\Unit\Kafka\Consumer;

use LimLabs\KafkaBundle\Exception\MissingConsumerGroupException;
use LimLabs\KafkaBundle\Exception\MissingSubscribedTopicsException;
use LimLabs\KafkaBundle\Kafka\Consumer\ConsumerConfiguration;
use PHPUnit\Framework\TestCase;

class ConsumerConfigurationTest extends TestCase
{
    public function testCreateConfigurationWithoutDefaultValues(): void
    {
        $configuration = ConsumerConfiguration::createConfiguration([
            'consumer_group' => 'test_group',
            'connection' => 'test1',
            'offset_reset' => 'offset_test',
            'subscribed_topics' => ['test:9092'],
        ]);

        $this->assertSame('test_group', $configuration->getConsumerGroup());
        $this->assertSame('test1', $configuration->getConnection());
        $this->assertSame('offset_test', $configuration->getOffsetReset());
        $this->assertSame(['test:9092'], $configuration->getSubscribedTopics());
    }

    public function testCreateConfigurationWithDefaultValues(): void
    {
        $configuration = ConsumerConfiguration::createConfiguration([
            'consumer_group' => 'test_group',
            'subscribed_topics' => ['test:9092'],
        ]);

        $this->assertSame('test_group', $configuration->getConsumerGroup());
        $this->assertSame('default', $configuration->getConnection());
        $this->assertSame('earliest', $configuration->getOffsetReset());
        $this->assertSame(['test:9092'], $configuration->getSubscribedTopics());
    }

    public function testCreateConfigurationWithMissingConsumerGroup(): void
    {
        $this->expectExceptionObject(new MissingConsumerGroupException());
        ConsumerConfiguration::createConfiguration([
            'subscribed_topics' => ['test:9092'],
        ]);
    }

    public function testCreateConfigurationWithMissingTopicsGroup(): void
    {
        $this->expectExceptionObject(new MissingSubscribedTopicsException());
        ConsumerConfiguration::createConfiguration([
            'consumer_group' => 'test_group',
        ]);
    }
}