<?php

namespace LimLabs\KafkaBundle\Service;

use LimLabs\KafkaBundle\Enum\ConsumerResponse;
use LimLabs\KafkaBundle\Exception\CouldNotCreateTopic;
use LimLabs\KafkaBundle\Exception\NoDefaultConfigurationExisting;
use LimLabs\KafkaBundle\Exception\RequestedKafkaClientNonExisting;
use LimLabs\KafkaBundle\Factory\KafkaFactory;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use RdKafka\Message;

class ResponseHandler
{
    private KafkaConsumer $consumer;
    private Message $message;

    public function __construct(
        private readonly KafkaFactory $kafkaFactory,
        private readonly CliMessageService $cliMessageService,
    ) {
    }

    public function handleConsumerResponse(
        ConsumerResponse $response,
        KafkaConsumer $consumer,
        Message $message,
    ): void {
        $this->consumer = $consumer;
        $this->message = $message;

        if ($response == ConsumerResponse::SUCCESS) {
            return;
        }

        if ($response == ConsumerResponse::ERROR_DROP) {
            $this->cliMessageService->errorDroppedMessage();
        }

        if ($response == ConsumerResponse::ERROR_REQUEUE) {
            try {
                $this->requeueMessage();
                $this->cliMessageService->errorRequeuedMessage();
            } catch (CouldNotCreateTopic|RequestedKafkaClientNonExisting $e) {
                $this->cliMessageService->writeErrorMessageWithDate($e->getMessage());
            }
        }
    }

    /**
     * @throws RequestedKafkaClientNonExisting
     * @throws CouldNotCreateTopic|NoDefaultConfigurationExisting
     */
    private function requeueMessage(): void
    {
        $configuration = $this->consumer->getConsumerConfiguration();
        $connection = $configuration->getConnection();
        $topic = $this->message->topic_name;

        $kafkaClient = $this->kafkaFactory->getKafkaClient($connection);
        $producer = $kafkaClient->getProducer();

        $producer->createTopic($topic)->produce($this->message->payload);
        $producer->flush();
    }
}