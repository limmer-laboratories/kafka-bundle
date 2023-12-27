<?php

namespace LimLabs\KafkaBundle\Tests\Unit\Service;

use LimLabs\KafkaBundle\Command\TopicConsumeCommand;
use LimLabs\KafkaBundle\Kafka\Consumer\KafkaConsumer;
use LimLabs\KafkaBundle\Service\CliMessageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

class CliMessageServiceTest extends TestCase
{
    public function testConsumerRunning(): void
    {
        $consumer = $this->createMock(KafkaConsumer::class);

        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle->expects($this->once())
            ->method('info')
            ->with($this->matchesRegularExpression('/\d{2}:\d{2} \d{2}-\d{2}-\d{4} Consumer \[.*\] running\.\.\. Waiting for messages/'));

        TopicConsumeCommand::$currentSymfonyStyle = $symfonyStyle;

        $cliMessageService = new CliMessageService();
        $cliMessageService->consumerRunning($consumer);
    }

    public function testConsumingMessage(): void
    {
        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle->expects($this->once())
            ->method('info')
            ->with($this->matchesRegularExpression('/\d{2}:\d{2} \d{2}-\d{2}-\d{4} Consuming a message/'));

        TopicConsumeCommand::$currentSymfonyStyle = $symfonyStyle;

        $cliMessageService = new CliMessageService();
        $cliMessageService->consumingMessage();
    }

    public function testErrorDroppedMessage(): void
    {
        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle->expects($this->once())
            ->method('error')
            ->with($this->matchesRegularExpression('/\d{2}:\d{2} \d{2}-\d{2}-\d{4} An error occurred while consuming a message. The message got dropped/'));

        TopicConsumeCommand::$currentSymfonyStyle = $symfonyStyle;

        $cliMessageService = new CliMessageService();
        $cliMessageService->errorDroppedMessage();
    }

    public function testErrorRequeuedMessage(): void
    {
        $symfonyStyle = $this->createMock(SymfonyStyle::class);
        $symfonyStyle->expects($this->once())
            ->method('error')
            ->with($this->matchesRegularExpression('/\d{2}:\d{2} \d{2}-\d{2}-\d{4} An error occurred while consuming a message. The message got requeued/'));

        TopicConsumeCommand::$currentSymfonyStyle = $symfonyStyle;

        $cliMessageService = new CliMessageService();
        $cliMessageService->errorRequeuedMessage();
    }
}