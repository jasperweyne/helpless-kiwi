<?php

namespace App\Command;

use App\Security\AuthUserProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAuthIdCommand extends Command
{
    private $userProvider;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-auth-id';

    public function __construct(AuthUserProvider $userProvider)
    {
        $this->userProvider = $userProvider;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Sets a login for a Person.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a auth ID for an e-mail address...')

            // possible arguments
            ->addArgument('email', InputArgument::REQUIRED, 'The e-mail address of the login.')
            ->addArgument('key', InputArgument::OPTIONAL, 'User provider key to encode the auth ID with (optional).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $key = null;
        if ($input->hasArgument('key')) {
            $key = $input->getArgument('key');
        }

        $output->writeln('ID: ' . $this->userProvider->usernameHash($email, $key));
    }
}
