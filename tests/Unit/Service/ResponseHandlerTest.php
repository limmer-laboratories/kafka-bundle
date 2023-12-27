<?php

namespace LimLabs\KafkaBundle\Tests\Unit\Service;

use LimLabs\KafkaBundle\Enum\ConsumerResponse;
use LimLabs\KafkaBundle\Exception\RequestedKafkaClientNonExisting;
use LimLabs\KafkaBundle\Factory\KafkaFactory;
use LimLabs\KafkaBundle\Kafka\Consumer\ConsumerConfiguration;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use LimLabs\KafkaBundle\Kafka\KafkaClient;
use LimLabs\KafkaBundle\Kafka\KafkaProducer;
use LimLabs\KafkaBundle\Kafka\KafkaTopic;
use LimLabs\KafkaBundle\Service\CliMessageService;
use LimLabs\KafkaBundle\Service\ResponseHandler;
use PHPUnit\Framework\TestCase;
use RdKafka\Message;

class ResponseHandlerTest extends TestCase
{
    public function testHandleConsumerResponseSuccess(): void
    {
        $mockKafkaFactory = $this->createMock(KafkaFactory::class);
        $mockCliMessageService = $this->createMock(CliMessageService::class);
        $mockConsumer = $this->createMock(KafkaConsumer::class);
        $mockMessage = $this->createMock(Message::class);

        $mockCliMessageService->expects($this->never())
            ->method('errorDroppedMessage');

        $mockCliMessageService->expects($this->never())
            ->method('errorRequeuedMessage');

        $responseHandler = new ResponseHandler($mockKafkaFactory, $mockCliMessageService);
        $responseHandler->handleConsumerResponse(ConsumerResponse::SUCCESS, $mockConsumer, $mockMessage);
    }

    public function testHandleConsumerResponseErrorDrop(): void
    {
        $mockKafkaFactory = $this->createMock(KafkaFactory::class);
        $mockCliMessageService = $this->createMock(CliMessageService::class);
        $mockConsumer = $this->createMock(KafkaConsumer::class);
        $mockMessage = $this->createMock(Message::class);

        $responseHandler = new ResponseHandler($mockKafkaFactory, $mockCliMessageService);

        $mockCliMessageService->expects($this->once())
            ->method('errorDroppedMessage');

        $responseHandler->handleConsumerResponse(ConsumerResponse::ERROR_DROP, $mockConsumer, $mockMessage);
    }

    public function testHandleConsumerResponseErrorRequeue(): void
    {
        $kafkaFactory = $this->createMock(KafkaFactory::class);
        $kafkaClient = $this->createMock(KafkaClient::class);
        $producer = $this->createMock(KafkaProducer::class);
        $kafkaTopic = $this->createMock(KafkaTopic::class);
        $cliMessageService = $this->createMock(CliMessageService::class);
        $consumer = $this->createMock(KafkaConsumer::class);

        $message = new Message();
        $message->topic_name = 'test_topic';
        $message->payload = 'test_payload';

        $connectionName = 'test_connection';

        $configuration = $this->createMock(ConsumerConfiguration::class);

        $configuration->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionName);

        $consumer->expects($this->once())
            ->method('getConsumerConfiguration')
            ->willReturn($configuration);

        $kafkaTopic->expects($this->once())
            ->method('produce')
            ->with($message->payload);

        $producer->expects($this->once())
            ->method('createTopic')
            ->with($message->topic_name)
            ->willReturn($kafkaTopic);

        $producer->expects($this->once())
            ->method('flush');

        $kafkaClient->expects($this->once())
            ->method('getProducer')
            ->willReturn($producer);

        $kafkaFactory->expects($this->once())
            ->method('getKafkaClient')
            ->with($connectionName)
            ->willReturn($kafkaClient);

        $cliMessageService->expects($this->once())
            ->method('errorRequeuedMessage');
        $cliMessageService->expects($this->never())
            ->method('writeErrorMessageWithDate');

        $responseHandler = new ResponseHandler($kafkaFactory, $cliMessageService);
        $responseHandler->handleConsumerResponse(ConsumerResponse::ERROR_REQUEUE, $consumer, $message);
    }

    public function testHandleConsumerResponseErrorRequeueFailing(): void
    {
        $kafkaFactory = $this->createMock(KafkaFactory::class);
        $cliMessageService = $this->createMock(CliMessageService::class);
        $consumer = $this->createMock(KafkaConsumer::class);
        $message = $this->createMock(Message::class);

        $connectionName = 'test_connection';

        $configuration = $this->createMock(ConsumerConfiguration::class);

        $configuration->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionName);

        $consumer->expects($this->once())
            ->method('getConsumerConfiguration')
            ->willReturn($configuration);

        $kafkaFactory->expects($this->once())
            ->method('getKafkaClient')
            ->willThrowException(new RequestedKafkaClientNonExisting());

        $cliMessageService->expects($this->never())
            ->method('errorRequeuedMessage');
        $cliMessageService->expects($this->once())
            ->method('writeErrorMessageWithDate');

        $responseHandler = new ResponseHandler($kafkaFactory, $cliMessageService);
        $responseHandler->handleConsumerResponse(ConsumerResponse::ERROR_REQUEUE, $consumer, $message);
    }
}