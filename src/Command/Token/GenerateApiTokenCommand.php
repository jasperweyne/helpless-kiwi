<?php

namespace App\Command\Token;

use App\Entity\Security\LocalAccount;
use App\Entity\Security\TrustedClient;
use App\Repository\ApiTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'token:generate',
    description: 'Generate an API token for a given user.',
)]
class GenerateApiTokenCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private ApiTokenRepository $tokenRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username to generate an API token for.')
            ->addArgument('client', InputArgument::REQUIRED, 'The trusted client name that will hold the API token.')
            ->addArgument('expires_at', InputArgument::OPTIONAL, 'When the generated API token will expire.', '+5 minutes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $clientId = $input->getArgument('client');
        $expiresAt = $input->getArgument('expires_at');

        // Get client and validate its existence
        if (null === $user = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => $username])) {
            $io->error("A user with username '{$username}' doesn't exist.");

            return Command::FAILURE;
        }

        // Get client and validate its existence
        if (null === $client = $this->em->getRepository(TrustedClient::class)->find($clientId)) {
            $io->error("A client named '{$clientId}' doesn't exist.");

            return Command::FAILURE;
        }

        $token = $this->tokenRepository->generate($user, $client, new \DateTimeImmutable($expiresAt));

        $io->success($token);

        return Command::SUCCESS;
    }
}
