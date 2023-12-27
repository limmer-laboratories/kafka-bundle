# A Symfony Kafka Bundle
- [Installation](#installation)
- [Usage](#usage)
  - [Configuration](#configuration)
  - [Sending messages to kafka](#sending-messages-to-kafka)
  - [Consuming kafka topics](#consuming-kafka-topics)
    - [Implementing a consumer](#implementing-a-consumer)
    - [Executing a consumer](#executing-a-consumer)

# Installation
To install the symfony kafka bundle just execute the following composer command:
```shell
composer require limlabs/kafka-bundle
```
# Usage
## Configuration
To use the kafka bundle you have to configure at least the `default` connection.  
Example of such a configuration:
```yaml
# config/packages/kafka.yaml

kafka:
  clients:
    default:
      brokers: 'kafka:9092' #A comma separated list of kafka brokers
      log_level: LOG_DEBUG #The rdkafka log level (PHP-Constants)
      debug: all #Which debug information should be printed by rdkafka. You can remove this to disable it
```
If you have multiple specific kafka connections, you can add multiple `clients` to this configuration.
```yaml
# config/packages/kafka.yaml

kafka:
  clients:
    default:
      brokers: 'kafka:9092'
      log_level: LOG_DEBUG
      debug: all
    different_client:
      brokers: 'kafka2:9092,kafka3:9092'
      log_level: LOG_ERROR
```

## Sending messages to kafka
To send message to a topic over the default configuration you can simply use the factory to get the `KafkaClient`. 
From the `KafkaClient` you can get the `producer` for your kafka connection. With this producer you can create a `topic` in which you can send a message with the `produce` function.
The `createTopic` automatically gets an existing topic or creates a new one, if the specified topic does not exist. 
Here an example of this in a symfony controller:
```php
namespace App\Controller;

use LimLabs\KafkaBundle\Factory\KafkaFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExampleController extends AbstractController
{
    public function __construct(
        private readonly KafkaFactory  $kafkaFactory
    ) {
    }

    #[Route('/example', name: 'app_example')]
    public function index(): JsonResponse
    {
        $client = $this->kafkaFactory->getKafkaClient();
        $producer = $client->getProducer();
        $producer->createTopic('test')->produce('TestMessage');
        $producer->flush();

        [...]
    }
```
> :warning: Don't forget the `$producer->flush()` method call. Without it, you will lose data!

To get a `KafkaClient` for a specific connection, just set the name of the desired connection as a parameter in the `getKafkaClient` function of the factory.  
```php
$client = $this->kafkaFactory->getKafkaClient('different_client');
```

## Consuming kafka topics
### Implementing a consumer
To consume kafka topics, you need to setup a class which implements the `KafkaConsumer`-Interface.
This interface automatically tags the class correctly for symfony DI and forces you to implement the `consume` and the `getConsumerConfiguration` functions.  
Here is a quick example of this:
```php
<?php

namespace App\Consumers;

use LimLabs\KafkaBundle\Enum\ConsumerResponse;
use LimLabs\KafkaBundle\Kafka\Consumer\ConsumerConfiguration;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;

class ExampleQueueConsumer implements KafkaConsumer
{
    public function consume(string $message): ConsumerResponse
    {
        var_dump($message);
        return ConsumerResponse::SUCCESS;
    }

    public function getConsumerConfiguration(): ConsumerConfiguration
    {
        return ConsumerConfiguration::createConfiguration([
            'consumer_group' => 'example_consumer_group',
            'connection' => 'different_client', #if you do not set this 'default' will be used
            'subscribed_topics' => [
                'example_topic'
            ]
        ]);
    }
}
```
In the `getConsumerConfiguration` function you have to return a `ConsumerConfiguration`-Object.
This describes the specific configuration for the consumer. The array keys `consumer_group` and `subscribed_topics` are required.
Without these, an exception will be thrown.  
You can create the configuration object like in the previous example or like this:
```php
public function getConsumerConfiguration(): ConsumerConfiguration
{
    $configuration = new ConsumerConfiguration();
    $configuration->setConnection('different_client');
    $configuration->setConsumerGroup('example_consumer_group');
    $configuration->setSubscribedTopics(['example_topic']);
    return $configuration; 
}
```
The `consume` function gets called on each message that comes in over the specified topics. 
This function has to return a `ConsumerResponse`.  
The following responses are valid:
```php
ConsumerResponse::SUCCESS;
ConsumerResponse::ERROR_DROP;
ConsumerResponse::ERROR_REQUEUE;
```
The `SUCCESS` response signalises the executor that everything gone well. The `ERROR_DROP` response
informs the executor of an error. In this case, an error message will be logged in the terminal, but the message will be dropped if you don't take care of it yourself!  
In return of `ERROR_DROP`, there is `ERROR_REQUEUE`, which sends the payload of the message back to the Kafka topic from which it came to prevent data loss.
> :warning: The use of `ERROR_REQUEUE` can lead to infinit loops between the consumer and Kafka.

### Executing a consumer
To list all via symfony DI loaded consumers, you can use the following command:
```shell
bin/console kafka:consumers:list
```
Which would return something like this:
```
----------------------------------- 
Name                             
-----------------------------------
App\Consumers\ExampleQueueConsumer  
-----------------------------------
```
Now to start a consumer use the following command:
```shell
bin/console kafka:topic:consume [CLASS_PATH]
```
If you don't give the class path as an argument, the executor will automatically start the first loaded consumer.
If the consumer started correctly you will be greeted by the following message:
```
 [INFO] 20:11 27-12-2023 Consumer [App\Consumers\ExampleQueueConsumer] running... Waiting for messages
```
:tada: Congrats now is your consumer consuming messages! 