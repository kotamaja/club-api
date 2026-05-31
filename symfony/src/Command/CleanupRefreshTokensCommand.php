<?php

namespace App\Command;

use App\Repository\RefreshTokenRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:auth:cleanup-refresh-tokens',
    description: 'Delete expired refresh tokens and old revoked refresh tokens.',
)]
final class CleanupRefreshTokensCommand extends Command
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'revoked-retention-days',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of days to keep revoked refresh tokens before deletion.',
                7
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show cleanup criteria without deleting anything.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $revokedRetentionDays = (int)$input->getOption('revoked-retention-days');

        if ($revokedRetentionDays < 0) {
            $output->writeln('<error>revoked-retention-days must be greater than or equal to 0.</error>');

            return Command::INVALID;
        }

        $now = new \DateTimeImmutable();

        $expiredBefore = $now;
        $revokedBefore = $now->modify(sprintf('-%d days', $revokedRetentionDays));

        if ((bool)$input->getOption('dry-run')) {
            $output->writeln('<info>Dry run only. No refresh token will be deleted.</info>');
            $output->writeln(sprintf('Would delete tokens expired before: <comment>%s</comment>', $expiredBefore->format(\DateTimeInterface::ATOM)));
            $output->writeln(sprintf('Would delete tokens revoked before: <comment>%s</comment>', $revokedBefore->format(\DateTimeInterface::ATOM)));
            return Command::SUCCESS;
        }

        $deletedCount = $this->refreshTokenRepository->deleteExpiredOrOldRevoked(
            expiredBefore: $expiredBefore,
            revokedBefore: $revokedBefore,
        );

        $output->writeln(sprintf(
            '<info>Deleted %d refresh token(s).</info>',
            $deletedCount
        ));

        return Command::SUCCESS;
    }
}
