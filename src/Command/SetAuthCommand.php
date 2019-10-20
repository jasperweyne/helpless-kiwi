<?php

namespace App\Command;

use App\Entity\Person\Person;
use App\Entity\Security\Auth;
use App\Security\AuthUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Console\Input\InputOption;

class SetAuthCommand extends Command
{
    private $em;
    private $userProvider;
    private $passwordEncoder;
    private $raw_pass;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:set-auth';

    public function __construct(EntityManagerInterface $em, AuthUserProvider $userProvider, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->userProvider = $userProvider;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Sets a login for a Person.')

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
            'Auth Creator',
            '============',
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

        $person = $this->em->getRepository(Person::class)->findOneBy(['email' => $email]);
        if (null === $person) {
            throw new \Exception('Person for given email not found.');
        }

        $auth = $person->getAuth() ?? new Auth();
        $auth
            // Persons
            ->setPerson($person)
            ->setAuthId($this->userProvider->usernameHash($email))
            ->setPassword($this->passwordEncoder->encodePassword($auth, $this->raw_pass))
        ;

        if (false !== $input->getOption('admin')) {
            $auth->setRoles(['ROLE_ADMIN']);
        }

        $this->em->persist($auth);
        $this->em->flush();

        $output->writeln($person->getCanonical().' login registered!');
    }
}
