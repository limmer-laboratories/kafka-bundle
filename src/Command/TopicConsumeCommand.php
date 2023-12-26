<?php

namespace LimLabs\KafkaBundle\Command;

use LimLabs\KafkaBundle\Exception\ConsumerNotImplementedKafkaConsumerException;
use LimLabs\KafkaBundle\Exception\NoConsumersRegisteredException;
use LimLabs\KafkaBundle\Exception\RequestedConsumerNotExistingException;
use LimLabs\KafkaBundle\Exception\RequestedKafkaClientNonExisting;
use LimLabs\KafkaBundle\Service\ConsumerExecutor;
use RdKafka\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'kafka:topic:consume')]
class TopicConsumeCommand extends Command
{
    public static ?SymfonyStyle $currentSymfonyStyle = null;
    protected static $defaultDescription = 'Consume a kafka topic';

    public function __construct(
        private readonly ConsumerExecutor $consumerExecutor,
        $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('consumer', InputArgument::OPTIONAL);
    }

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     * @throws RequestedConsumerNotExistingException
     * @throws Exception
     * @throws RequestedKafkaClientNonExisting
     * @throws NoConsumersRegisteredException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        TopicConsumeCommand::$currentSymfonyStyle = new SymfonyStyle($input, $output);

        $this->consumerExecutor->executeConsumer($input->getArgument('consumer'));
        return Command::SUCCESS;
    }

}