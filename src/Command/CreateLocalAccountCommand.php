<?php

namespace App\Command;

use App\Entity\Security\LocalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateLocalAccountCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserPasswordHasherInterface
     */
    private $userpasswordHasher;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-account';

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $userpasswordHasher)
    {
        $this->em = $em;
        $this->userpasswordHasher = $userpasswordHasher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list" ->setDescription('Creates a local account.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a login...')

            // possible arguments
            ->addArgument('email', InputArgument::REQUIRED, 'The e-mail address of the login.')
            ->addArgument('name', InputArgument::OPTIONAL, 'The public name associated with the user.')
            ->addArgument('pass', InputArgument::OPTIONAL, 'The password for login with the user.')

            // options
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Make the user an admin');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln([
            'Local Account Creator',
            '=====================',
            '',
        ]);
        $helper = $this->getHelper('question');

        if (is_null($input->getArgument('name'))) {
            $question = new Question('Public name: ');
            /** @var string $name */
            $name = $helper->ask($input, $output, $question);
            $input->setArgument('name', $name);
        }

        if (is_null($input->getArgument('pass'))) {
            while (true) {
                $question = new Question('Please enter a password: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                /** @var string $pass */
                $pass = $helper->ask($input, $output, $question);

                $question = new Question('Confirm the password: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $pass_confirm = $helper->ask($input, $output, $question);

                if ($pass === $pass_confirm && '' != $pass) {
                    $input->setArgument('pass', $pass);
                    break;
                }

                $output->writeln('Invalid input, please try again!');
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        $pass = $input->getArgument('pass');
        assert(is_string($name));
        assert(is_string($pass));

        $account = $this->em->getRepository(LocalAccount::class)->findOneBy(['email' => $email]) ?? new LocalAccount();
        $hashedPass = $this->userpasswordHasher->hashPassword($account, $pass);
        $account
            // Persons
            ->setName($name)
            ->setEmail($email)
            ->setPassword($hashedPass)
            ->setRoles($input->getOption('admin') ? ['ROLE_ADMIN'] : []);

        $this->em->persist($account);
        $this->em->flush();

        $output->writeln($account->getCanonical() . ' login registered!');

        return 0;
    }
}
