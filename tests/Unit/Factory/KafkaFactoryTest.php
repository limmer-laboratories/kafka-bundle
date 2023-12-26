<?php

namespace LimLabs\KafkaBundle\Tests\Unit\Factory;

use LimLabs\KafkaBundle\Exception\NoDefaultConfigurationExisting;
use LimLabs\KafkaBundle\Exception\RequestedKafkaClientNonExisting;
use LimLabs\KafkaBundle\Factory\KafkaFactory;
use LimLabs\KafkaBundle\Kafka\KafkaClient;
use PHPUnit\Framework\TestCase;

class KafkaFactoryTest extends TestCase
{
    public function testGetDefaultKafkaClient(): void
    {
        $configArray = [
            'default' => $this->getClientConfiguration(),
        ];

        $factory = new KafkaFactory($configArray);
        $client = $factory->getKafkaClient();

        $this->validateKafkaClient($configArray, 'default', $client);
    }

    public function testGetSpecificKafkaClient(): void
    {
        $configArray = [
            'test1' => $this->getClientConfiguration(),
        ];

        $factory = new KafkaFactory($configArray);
        $client = $factory->getKafkaClient('test1');

        $this->validateKafkaClient($configArray, 'test1', $client);
    }

    public function testGetDefaultClientWithoutDefaultConfiguration(): void
    {
        $configArray = [];

        $factory = new KafkaFactory($configArray);

        $this->expectExceptionObject(new NoDefaultConfigurationExisting());
        $factory->getKafkaClient();
    }

    public function testGetInvalidKafkaConnection(): void
    {
        $configArray = [];

        $factory = new KafkaFactory($configArray);

        $this->expectExceptionObject(new RequestedKafkaClientNonExisting());
        $factory->getKafkaClient('nonExistingTest');
    }

    private function validateKafkaClient(array $config, string $clientName, KafkaClient $client): void
    {
        $this->assertSame($config[$clientName]['brokers'], $client->getBrokers());

        $clientConfiguration = $client->getConfig()->dump();
        $this->assertSame((string) get_defined_constants()[$config[$clientName]['log_level']], $clientConfiguration['log_level']);
        $this->assertSame('generic,broker,topic,metadata,feature,queue,msg,protocol,cgrp,security,fetch,interceptor,plugin,consumer,admin,eos,mock,assignor,conf,all', $clientConfiguration['debug']);
    }

    private function getClientConfiguration(): array
    {
        return [
            'log_level' => 'LOG_EMERG',
            'debug' => 'all',
            'brokers' => 'test:9092',
        ];
    }
}