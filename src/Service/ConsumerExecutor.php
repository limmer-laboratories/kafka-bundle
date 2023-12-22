<?php

namespace LimLabs\KafkaBundle\Service;

use LimLabs\KafkaBundle\Exception\ConsumerNotImplementedKafkaConsumerException;
use LimLabs\KafkaBundle\Exception\ConsumingException;
use LimLabs\KafkaBundle\Exception\NoConsumersRegisteredException;
use LimLabs\KafkaBundle\Exception\RequestedConsumerNotExistingException;
use LimLabs\KafkaBundle\Exception\RequestedKafkaClientNonExisting;
use LimLabs\KafkaBundle\Factory\KafkaFactory;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use RdKafka\Conf;
use RdKafka\Exception;
use RdKafka\Message;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsumerExecutor
{
    private KafkaConsumer $consumer;
    private Conf $config;
    private \RdKafka\KafkaConsumer $kafkaConsumer;
    private SymfonyStyle $io;

    public function __construct(
        private readonly ConsumerService $consumerService,
        private readonly KafkaFactory $kafkaFactory,
    )
    {
    }

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     * @throws RequestedConsumerNotExistingException
     * @throws NoConsumersRegisteredException
     * @throws RequestedKafkaClientNonExisting
     * @throws Exception
     */
    public function executeConsumer(SymfonyStyle $io, ?string $name = ''): void
    {
        $this->io = $io;

        $this->loadConsumer($name);

        $this->registerConsumerToTopic();
        $this->createKafkaConsumer();
        $this->subscribeToTopics();

        $this->consumeMessages();
    }

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     * @throws RequestedConsumerNotExistingException
     * @throws NoConsumersRegisteredException
     */
    private function loadConsumer(?string $name = ''): void
    {
        $consumers = $this->consumerService->getListOfConsumers();

        $this->consumer = empty($name) ?
            $this->consumerService->getDefaultConsumer() :
            $this->consumerService->filterForRequestedConsumer($consumers, $name);
    }

    /**
     * @throws RequestedKafkaClientNonExisting
     */
    private function registerConsumerToTopic(): void
    {
        $this->config = new Conf();
        $this->config->set('group.id', $this->consumer->getConsumerConfiguration()->getConsumerGroup());
        $this->config->set('metadata.broker.list', $this->getKafkaBrokerList());
        $this->config->set('auto.offset.reset', $this->consumer->getConsumerConfiguration()->getOffsetReset());
        $this->config->set('enable.partition.eof', 'true');
    }

    private function createKafkaConsumer(): void
    {
        $this->kafkaConsumer = new \RdKafka\KafkaConsumer($this->config);
    }

    /**
     * @throws Exception
     */
    private function subscribeToTopics(): void
    {
        $this->kafkaConsumer->subscribe($this->consumer->getConsumerConfiguration()->getSubscribedTopics());
    }

    /**
     * @throws RequestedKafkaClientNonExisting
     */
    private function getKafkaBrokerList(): string
    {
        $client = $this->kafkaFactory->getKafkaClient($this->consumer->getConsumerConfiguration()->getConnection());
        return $client->getBrokers();
    }

    private function consumeMessages(): void
    {
        $this->io->info('Consumer running... Waiting for messages');

        while (true) {
            $message = $this->kafkaConsumer->consume(120*1000);

            $this->io->info('Consuming message');

            $this->handleMessageError($message);
            $this->consumer->consume($message->payload);

//            $this->kafkaConsumer->commit($message);
        }
    }

    /**
     * @throws ConsumingException
     */
    private function handleMessageError(Message $message): void
    {
        if ($message->err != RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new ConsumingException($message->errstr());
        }
    }
}