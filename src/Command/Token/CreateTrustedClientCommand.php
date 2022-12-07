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

#[AsCommand(
    name: 'token:client:create',
    description: 'Create a id/secret for a trusted client for API access.',
)]
class CreateTrustedClientCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'A unique identifier for the trusted client')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        // Check for duplicates
        if (null !== $this->em->getRepository(TrustedClient::class)->find($name)) {
            $io->error("A client named '{$name}' already exists, remove it first or choose a different name.");
            return Command::FAILURE;
        }

        // Build the trusted client
        $secret = base64_encode(random_bytes(1024 / 8));
        $client = new TrustedClient($name, $secret);

        // Flush to database
        $this->em->persist($client);
        $this->em->flush();

        $io->writeln("Secret: {$secret}");
        $io->success("Client '{$name}' generated!");
        return Command::SUCCESS;
    }
}
