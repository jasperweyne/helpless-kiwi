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

class CreateLocalAccountCommand extends Command
{
    private $em;
    private $passwordEncoder;
    private $raw_pass;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-account';

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

            // options
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Make the user an admin');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Local Account Creator',
            '=====================',
            '',
        ]);
        $helper = $this->getHelper('question');

        while (true) {
            $question = new Question('Please enter a password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $pass = $helper->ask($input, $output, $question);

            $question = new Question('Confirm the password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $pass_confirm = $helper->ask($input, $output, $question);

            if ($pass === $pass_confirm && '' != $pass) {
                $this->raw_pass = $pass;
                break;
            }

            $output->writeln('Invalid input, please try again!');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $account = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => $email]) ?? new LocalAccount();
        $account
            // Persons
            ->setEmail($email)
            ->setPassword($this->passwordEncoder->encodePassword($account, $this->raw_pass))
            ->setRoles($input->getOption('admin') ? ['ROLE_ADMIN'] : [])
        ;

        $this->em->persist($account);
        $this->em->flush();

        $output->writeln($account->getPerson()->getCanonical().' login registered!');
    }
}
