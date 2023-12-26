<?php

namespace LimLabs\KafkaBundle\Service;

use DateTime;
use LimLabs\KafkaBundle\Command\TopicConsumeCommand;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use Symfony\Component\Console\Style\SymfonyStyle;

class CliMessageService
{
    private ?SymfonyStyle $io;

    public function __construct()
    {
        $this->io = TopicConsumeCommand::$currentSymfonyStyle;
    }

    public function consumerRunning(KafkaConsumer $consumer): void
    {
        $consumerName = get_class($consumer);
        $this->writeInfoMessageWithDate("Consumer [$consumerName] running... Waiting for messages");
    }

    public function consumingMessage(): void
    {
        $this->writeInfoMessageWithDate('Consuming a message');
    }

    public function errorDroppedMessage(): void
    {
        $this->writeErrorMessageWithDate('An error occurred while consuming a message. The message got dropped');
    }

    public function errorRequeuedMessage(): void
    {
        $this->writeErrorMessageWithDate('An error occurred while consuming a message. The message got requeued');
    }

    public function writeInfoMessageWithDate(string $message): void
    {
        $this->refreshIO();
        $this->io?->info($this->getFormattedDate() . ' ' . $message);
    }

    public function writeErrorMessageWithDate(string $message): void
    {
        $this->refreshIO();
        $this->io?->error($this->getFormattedDate() . ' ' . $message);
    }

    private function getFormattedDate(): string
    {
        $dateTime = new DateTime();
        return $dateTime->format('H:i d-m-Y');
    }

    private function refreshIO(): void
    {
        $this->io = TopicConsumeCommand::$currentSymfonyStyle;
    }
}