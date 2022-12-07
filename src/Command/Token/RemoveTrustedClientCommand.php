<?php

namespace App\Command\Token;

use App\Entity\Security\TrustedClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsCommand(
    name: 'token:client:remove',
    description: 'Remove a trusted client and revoke all tokens.',
)]
class RemoveTrustedClientCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'The identifier of the trusted client')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        // Get client and validate its existence
        if (null === $client = $this->em->getRepository(TrustedClient::class)->find($name)) {
            $io->error("A client named '{$name}' doesn't exist.");
            return Command::FAILURE;
        }

        // Flush to database
        $this->em->remove($client);
        $this->em->flush();

        $io->success("Client '{$name}' removed.");
        return Command::SUCCESS;
    }
}
