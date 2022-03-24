<?php

namespace App\Command;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HasLocalAccountCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:has-account';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Checks whether local accounts exist.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command checks whether one or more local accounts exist.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accounts = $this->em->getRepository(LocalAccount::class)->findAll();
        $output->writeln(count($accounts) > 0 ? '1' : '0');
    }
}
