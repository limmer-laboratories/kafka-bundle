<?php

namespace LimLabs\KafkaBundle\Service;

use LimLabs\KafkaBundle\Exception\ConsumerNotImplementedKafkaConsumerException;
use LimLabs\KafkaBundle\Exception\ConsumingException;
use LimLabs\KafkaBundle\Exception\NoConsumersRegisteredException;
use LimLabs\KafkaBundle\Exception\NoDefaultConfigurationExisting;
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

    public function __construct(
        private readonly ConsumerService $consumerService,
        private readonly KafkaFactory $kafkaFactory,
        private readonly ResponseHandler $responseHandler,
        private readonly CliMessageService $cliMessageService,
    )
    {
    }

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     * @throws RequestedConsumerNotExistingException
     * @throws NoConsumersRegisteredException
     * @throws RequestedKafkaClientNonExisting
     * @throws Exception|NoDefaultConfigurationExisting
     */
    public function executeConsumer(?string $name = ''): void
    {
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
     * @throws RequestedKafkaClientNonExisting|NoDefaultConfigurationExisting
     */
    private function registerConsumerToTopic(): void
    {
        $this->config = new Conf();
        $this->config->set('group.id', $this->consumer->getConsumerConfiguration()->getConsumerGroup());
        $this->config->set('metadata.broker.list', $this->getKafkaBrokerList());
        $this->config->set('auto.offset.reset', $this->consumer->getConsumerConfiguration()->getOffsetReset());
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
     * @throws RequestedKafkaClientNonExisting|NoDefaultConfigurationExisting
     */
    private function getKafkaBrokerList(): string
    {
        $client = $this->kafkaFactory->getKafkaClient($this->consumer->getConsumerConfiguration()->getConnection());
        return $client->getBrokers();
    }

    private function consumeMessages(): void
    {
        $this->cliMessageService->consumerRunning($this->consumer);

        while (true) {
            $message = $this->kafkaConsumer->consume(3600e3);

            $this->cliMessageService->consumingMessage();
            $this->handleMessageError($message);

            $response = $this->consumer->consume($message->payload);
            $this->responseHandler->handleConsumerResponse($response, $this->consumer, $message);
        }
    }

    /**
     * @throws ConsumingException
     */
    private function handleMessageError(Message $message): void
    {
        if ($message->err == RD_KAFKA_RESP_ERR_NO_ERROR) {
            return;
        }

        if ($message->err == RD_KAFKA_RESP_ERR__PARTITION_EOF) {
            return;
        }

        throw new ConsumingException($message->errstr());
    }
}