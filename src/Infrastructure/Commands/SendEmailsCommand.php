<?php

declare(strict_types=1);

namespace App\Infrastructure\Commands;

use App\Application\UseCase\Email\ProcessEmailQueueUseCase;
use App\Domain\Repository\EmailJobRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name:        'app:send-emails',
    description: 'Processes the email queue and sends pending emails (run every minute via cron).',
)]
final class SendEmailsCommand extends Command
{
    public function __construct(
        private readonly ProcessEmailQueueUseCase $processEmailQueue
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Sending Emails...');

        $result = $this->processEmailQueue->execute();

        if ($result['processed'] === 0 && $result['failed'] === 0) {
            $output->writeln('No emails to send.');
            return Command::SUCCESS;
        }

        $output->writeln("Result: {$result['processed']} sent, {$result['failed']} failed.");

        return $result['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
