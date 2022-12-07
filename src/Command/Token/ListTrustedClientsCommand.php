<?php

namespace App\Command\Token;

use App\Entity\Security\TrustedClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'token:client:list',
    description: 'List all avaiable trusted clients.',
)]
class ListTrustedClientsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $clients = $this->em->getRepository(TrustedClient::class)->findAll();
        $io->table(['name'], array_map(fn (TrustedClient $client) => [$client->id], $clients));

        return Command::SUCCESS;
    }
}
