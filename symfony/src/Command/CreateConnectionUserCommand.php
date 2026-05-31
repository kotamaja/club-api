<?php

namespace App\Command;

use App\Entity\ConnectionUser;
use App\Repository\ConnectionUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:connection-user:create',
    description: 'Create an active ConnectionUser with email and password.',
)]
final class CreateConnectionUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConnectionUserRepository $connectionUserRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address of the connection user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = mb_strtolower(trim((string) $input->getArgument('email')));

        $emailViolations = $this->validator->validate($email, [
            new Assert\NotBlank(),
            new Assert\Email(),
        ]);

        if (count($emailViolations) > 0) {
            $output->writeln('<error>Invalid email address.</error>');

            foreach ($emailViolations as $violation) {
                $output->writeln(sprintf('<error>- %s</error>', $violation->getMessage()));
            }

            return Command::INVALID;
        }

        $existingUser = $this->connectionUserRepository->findOneBy([
            'email' => $email,
        ]);

        if ($existingUser !== null) {
            $output->writeln(sprintf(
                '<error>A ConnectionUser already exists with email "%s".</error>',
                $email
            ));

            return Command::FAILURE;
        }

        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);

        $plainPassword = $questionHelper->ask($input, $output, $passwordQuestion);

        if (!is_string($plainPassword) || trim($plainPassword) === '') {
            $output->writeln('<error>Password cannot be empty.</error>');

            return Command::INVALID;
        }

        $confirmPasswordQuestion = new Question('Confirm password: ');
        $confirmPasswordQuestion->setHidden(true);
        $confirmPasswordQuestion->setHiddenFallback(false);

        $confirmedPassword = $questionHelper->ask($input, $output, $confirmPasswordQuestion);

        if ($plainPassword !== $confirmedPassword) {
            $output->writeln('<error>Passwords do not match.</error>');

            return Command::INVALID;
        }

        $passwordViolations = $this->validator->validate($plainPassword, [
            new Assert\NotBlank(),
            new Assert\Length(min: 6),
        ]);

        if (count($passwordViolations) > 0) {
            $output->writeln('<error>Invalid password.</error>');

            foreach ($passwordViolations as $violation) {
                $output->writeln(sprintf('<error>- %s</error>', $violation->getMessage()));
            }

            return Command::INVALID;
        }

        $connectionUser = new ConnectionUser($email);

        $passwordHash = $this->passwordHasher->hashPassword(
            $connectionUser,
            $plainPassword
        );

        $connectionUser->activate($passwordHash);

        $this->entityManager->persist($connectionUser);
        $this->entityManager->flush();

        $output->writeln(sprintf(
            '<info>ConnectionUser "%s" created successfully.</info>',
            $connectionUser->getEmail()
        ));

        $output->writeln(sprintf(
            '<info>Public ID: %s</info>',
            $connectionUser->getPublicId()
        ));

        return Command::SUCCESS;
    }
}
