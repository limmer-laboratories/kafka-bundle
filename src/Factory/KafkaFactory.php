<?php

namespace LimLabs\KafkaBundle\Factory;

use LimLabs\KafkaBundle\Exception\RequestedKafkaClientNonExisting;
use LimLabs\KafkaBundle\Kafka\KafkaClient;
use RdKafka\Conf;

class KafkaFactory
{
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @throws RequestedKafkaClientNonExisting
     */
    public function getKafkaClient(string $clientName = ""): KafkaClient
    {
        if (empty($clientName)) {
            return $this->createClientFromConfig($this->config['default']);
        }

        if (! isset($this->config[$clientName])) {
            throw new RequestedKafkaClientNonExisting();
        }

        return $this->createClientFromConfig($this->config[$clientName]);
    }

    private function createClientFromConfig(array $config): KafkaClient
    {
        $kafkaConfig = new Conf();

        $kafkaConfig->set('log_level', get_defined_constants()[$config['log_level']]);
        $kafkaConfig->set('debug', $config['debug']);

        return new KafkaClient($config['brokers'], $kafkaConfig);
    }
}