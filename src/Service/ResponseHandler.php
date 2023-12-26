<?php

namespace LimLabs\KafkaBundle\Service;

use LimLabs\KafkaBundle\Enum\ConsumerResponse;
use LimLabs\KafkaBundle\Exception\CouldNotCreateTopic;
use LimLabs\KafkaBundle\Exception\RequestedKafkaClientNonExisting;
use LimLabs\KafkaBundle\Factory\KafkaFactory;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use RdKafka\Message;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResponseHandler
{
    private KafkaConsumer $kafkaConsumer;
    private Message $message;

    public function __construct(
        private readonly KafkaFactory $kafkaFactory,
    ) {
    }

    public function handleConsumerResponse(
        ConsumerResponse $response,
        KafkaConsumer $consumer,
        Message $message,
        SymfonyStyle $io = null
    ): void {
        $this->kafkaConsumer = $consumer;
        $this->message = $message;

        if ($response == ConsumerResponse::SUCCESS) {
            return;
        }

        if ($response == ConsumerResponse::ERROR_DROP) {
            $io->error('An error occurred while consuming a message. The message got dropped');
        }

        if ($response == ConsumerResponse::ERROR_REQUEUE) {
            try {
                $this->requeueMessage();
                $io->error('An error occurred while consuming a message. The message got requeued');
            } catch (CouldNotCreateTopic|RequestedKafkaClientNonExisting $e) {
                $io->error($e->getMessage());
            }
        }
    }

    /**
     * @throws RequestedKafkaClientNonExisting
     * @throws CouldNotCreateTopic
     */
    private function requeueMessage(): void
    {
        $configuration = $this->kafkaConsumer->getConsumerConfiguration();
        $connection = $configuration->getConnection();
        $topic = $this->message->topic_name;

        $kafkaClient = $this->kafkaFactory->getKafkaClient($connection);
        $producer = $kafkaClient->getProducer();

        $producer->createTopic($topic)->produce($this->message->payload);
        $producer->flush();
    }
}