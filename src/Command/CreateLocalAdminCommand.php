<?php

namespace App\Command;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Console\Input\InputOption;

class CreateLocalAdminCommand extends Command
{
    private $em;
    private $passwordEncoder;
    private $raw_pass;
    private $name;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-admin';

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a local account.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a login...')

            // possible arguments
            ->addArgument('email', InputArgument::REQUIRED, 'The e-mail address of the login.')
            ->addArgument('name', InputArgument::REQUIRED, 'The e-mail address of the login.')
            ->addArgument('pass', InputArgument::REQUIRED, 'The e-mail address of the login.')

            // options
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Make the user an admin');
    }

    

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        $pass = $input->getArgument('pass');

        $account = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => $email]) ?? new LocalAccount();
        $account
            // Persons
            ->setName($name)
            ->setEmail($email)
            ->setPassword($this->passwordEncoder->encodePassword($account, $pass))
            ->setRoles($input->getOption('admin') ? ['ROLE_ADMIN'] : [])
        ;

        $this->em->persist($account);
        $this->em->flush();

        $output->writeln($account->getPerson()->getCanonical().' login registered!');
    }
}
