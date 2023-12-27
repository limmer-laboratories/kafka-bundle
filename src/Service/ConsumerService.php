<?php

namespace LimLabs\KafkaBundle\Service;

use LimLabs\KafkaBundle\Exception\ConsumerNotImplementedKafkaConsumerException;
use LimLabs\KafkaBundle\Exception\NoConsumersRegisteredException;
use LimLabs\KafkaBundle\Exception\RequestedConsumerNotExistingException;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ConsumerService
{
    public function __construct(
        #[TaggedIterator('kafka.consumer')]
        private readonly iterable $consumers
    ) {
    }

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     * @return KafkaConsumer[]
     */
    public function getListOfConsumers(): array
    {
        $consumers = [];

        foreach ($this->consumers as $consumer) {
            if (! $consumer instanceof KafkaConsumer) {
                throw new ConsumerNotImplementedKafkaConsumerException(get_class($consumer) . ' does not implement the KafkaConsumer interface');
            }
            $consumers[] = $consumer;
        }

        return $consumers;
    }

    /**
     * @param KafkaConsumer[] $consumers
     * @param string $name
     * @return KafkaConsumer
     * @throws RequestedConsumerNotExistingException
     */
    public function filterForRequestedConsumer(array $consumers, string $name): KafkaConsumer
    {
        foreach ($consumers as $consumer) {
            if (get_class($consumer) == $name) {
                return $consumer;
            }
        }

        throw new RequestedConsumerNotExistingException(
            "The '$name' consumer does not exist."
        );
    }

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     * @throws NoConsumersRegisteredException
     */
    public function getDefaultConsumer(): KafkaConsumer
    {
        $consumers = $this->getListOfConsumers();

        if (sizeof($consumers) <= 0) {
            throw new NoConsumersRegisteredException();
        }

        return $consumers[0];
    }
}