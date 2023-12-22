<?php

namespace LimLabs\KafkaBundle\Command;

use LimLabs\KafkaBundle\Exception\ConsumerNotImplementedKafkaConsumerException;
use LimLabs\KafkaBundle\Service\ConsumerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'kafka:consumers:list')]
class ConsumerListCommand extends Command
{
    public function __construct (
        private readonly ConsumerService $consumerService,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected static $defaultDescription = 'Show all registered consumers';

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $consumerNames = $this->getConsumerTableArray();
        $io->table(['Name'], $consumerNames);

        return Command::SUCCESS;
    }

    /**
     * @throws ConsumerNotImplementedKafkaConsumerException
     */
    private function getConsumerTableArray(): array
    {
        $consumers = $this->consumerService->getListOfConsumers();
        $consumerNames = [];

        foreach ($consumers as $consumer) {
            $consumerNames[] = [get_class($consumer)];
        }

        return $consumerNames;
    }
}