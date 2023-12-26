<?php

namespace LimLabs\KafkaBundle\Factory;

use LimLabs\KafkaBundle\Exception\NoDefaultConfigurationExisting;
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
     * @throws NoDefaultConfigurationExisting
     */
    public function getKafkaClient(string $clientName = ""): KafkaClient
    {
        if (empty($clientName)) {
            $this->checkDefaultConfiguration();
            return $this->createClientFromConfig($this->config['default']);
        }

        if (! isset($this->config[$clientName])) {
            throw new RequestedKafkaClientNonExisting();
        }

        return $this->createClientFromConfig($this->config[$clientName]);
    }

    /**
     * @throws NoDefaultConfigurationExisting
     */
    private function checkDefaultConfiguration(): void
    {
        if (! isset($this->config['default'])) {
            throw new NoDefaultConfigurationExisting();
        }
    }

    private function createClientFromConfig(array $config): KafkaClient
    {
        $kafkaConfig = new Conf();

        $kafkaConfig->set('log_level', get_defined_constants()[$config['log_level']]);

        if (isset($config['debug'])) {
            $kafkaConfig->set('debug', $config['debug']);
        }

        return new KafkaClient($config['brokers'], $kafkaConfig);
    }
}