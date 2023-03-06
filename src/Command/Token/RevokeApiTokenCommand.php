<?php

namespace App\Command\Token;

use App\Entity\Security\ApiToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'token:revoke',
    description: 'Revoke an issued API token.',
)]
class RevokeApiTokenCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('token', InputArgument::REQUIRED, 'The token to revoke.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tokenString = $input->getArgument('token');

        // Get client and validate its existence
        if (null === $token = $this->em->getRepository(ApiToken::class)->find($tokenString)) {
            $io->error("The provided token doesn't exist.");
            return Command::FAILURE;
        }

        $this->em->remove($token);
        $this->em->flush();

        $io->success('The token was revoked.');

        return Command::SUCCESS;
    }
}
