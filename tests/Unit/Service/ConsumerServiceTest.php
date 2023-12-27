<?php

namespace LimLabs\KafkaBundle\Tests\Unit\Service;

use LimLabs\KafkaBundle\Exception\ConsumerNotImplementedKafkaConsumerException;
use LimLabs\KafkaBundle\Exception\NoConsumersRegisteredException;
use LimLabs\KafkaBundle\Exception\RequestedConsumerNotExistingException;
use LimLabs\KafkaBundle\Kafka\Consumer\ConsumerConfiguration;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use LimLabs\KafkaBundle\Service\ConsumerService;
use PHPUnit\Framework\TestCase;

class ConsumerServiceTest extends TestCase
{
    public function testGetListOfConsumers(): void
    {
        $mockConsumer1 = $this->createMock(KafkaConsumer::class);
        $mockConsumer2 = $this->createMock(KafkaConsumer::class);
        $consumerService = new ConsumerService([$mockConsumer1, $mockConsumer2]);

        $consumers = $consumerService->getListOfConsumers();

        $this->assertCount(2, $consumers);
        $this->assertSame($mockConsumer1, $consumers[0]);
        $this->assertSame($mockConsumer2, $consumers[1]);
    }

    public function testGetListOfConsumersWithInvalidConsumer(): void
    {
        $mockConsumer1 = $this->createMock(KafkaConsumer::class);
        $consumerConfiguration = new ConsumerConfiguration();
        $consumerService = new ConsumerService([$mockConsumer1, $consumerConfiguration]);

        $this->expectExceptionMessage(get_class($consumerConfiguration) . " does not implement the KafkaConsumer interface");
        $consumerService->getListOfConsumers();
    }

    public function testFilterForRequestedConsumer(): void
    {
        $mockConsumer1 = $this->createMock(KafkaConsumer::class);
        $mockConsumer2 = $this->createMock(KafkaConsumer::class);
        $consumerService = new ConsumerService([$mockConsumer1, $mockConsumer2]);

        $filteredConsumer = $consumerService->filterForRequestedConsumer([$mockConsumer1, $mockConsumer2], get_class($mockConsumer1));

        $this->assertSame($mockConsumer1, $filteredConsumer);
    }

    public function testFilterForRequestedConsumerThrowsException(): void
    {
        $mockConsumer = $this->createMock(KafkaConsumer::class);
        $consumerService = new ConsumerService([$mockConsumer]);

        $this->expectException(RequestedConsumerNotExistingException::class);
        $consumerService->filterForRequestedConsumer([$mockConsumer], 'NonExistingConsumerClass');
    }

    public function testGetDefaultConsumer(): void
    {
        $mockConsumer = $this->createMock(KafkaConsumer::class);
        $consumerService = new ConsumerService([$mockConsumer]);

        $defaultConsumer = $consumerService->getDefaultConsumer();

        $this->assertSame($mockConsumer, $defaultConsumer);
    }

    public function testGetDefaultConsumerThrowsException(): void
    {
        $consumerService = new ConsumerService([]);

        $this->expectException(NoConsumersRegisteredException::class);
        $consumerService->getDefaultConsumer();
    }
}