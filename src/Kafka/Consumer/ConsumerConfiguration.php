<?php

namespace LimLabs\KafkaBundle\Kafka\Consumer;

use LimLabs\KafkaBundle\Exception\MissingConsumerGroupException;
use LimLabs\KafkaBundle\Exception\MissingSubscribedTopicsException;

class ConsumerConfiguration
{
    private string $consumerGroup;
    private string $connection = 'default';
    private string $offsetReset = 'earliest';
    private array $subscribedTopics = [];

    /**
     * @throws MissingConsumerGroupException
     * @throws MissingSubscribedTopicsException
     */
    public static function createConfiguration(array $configuration): ConsumerConfiguration
    {
        if (!isset($configuration['consumer_group'])) {
            throw new MissingConsumerGroupException();
        }

        if (!isset($configuration['subscribed_topics'])) {
            throw new MissingSubscribedTopicsException();
        }

        $consumerConfiguration = new ConsumerConfiguration();

        if (isset($configuration['connection'])) {
            $consumerConfiguration->setConnection($configuration['connection']);
        }

        if (isset($configuration['offset_reset'])) {
            $consumerConfiguration->setOffsetReset($configuration['offset_reset']);
        }

        $consumerConfiguration->setConsumerGroup($configuration['consumer_group']);
        $consumerConfiguration->setSubscribedTopics($configuration['subscribed_topics']);
        return $consumerConfiguration;
    }

    public function getConsumerGroup(): string
    {
        return $this->consumerGroup;
    }

    public function setConsumerGroup(string $consumerGroup): void
    {
        $this->consumerGroup = $consumerGroup;
    }

    public function getConnection(): string
    {
        return $this->connection;
    }

    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    public function getOffsetReset(): string
    {
        return $this->offsetReset;
    }

    public function setOffsetReset(string $offsetReset): void
    {
        $this->offsetReset = $offsetReset;
    }

    public function getSubscribedTopics(): array
    {
        return $this->subscribedTopics;
    }

    public function setSubscribedTopics(array $subscribedTopics): void
    {
        $this->subscribedTopics = $subscribedTopics;
    }
}