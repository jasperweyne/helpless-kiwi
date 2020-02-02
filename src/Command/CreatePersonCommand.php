<?php

namespace App\Command;

use App\Entity\Person\Person;
use App\Entity\Person\PersonValue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreatePersonCommand extends Command
{
    private $em;
    private $inputInfo;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-person';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new person.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a person...')

            // possible arguments
            ->addArgument('email', InputArgument::REQUIRED, 'The e-mail address of the user.')

            ->addArgument('name', InputArgument::IS_ARRAY, 'The full name of the user.')
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Person Creator',
            '==============',
            '',
        ]);

        // Gather initial info
        $this->inputInfo = [
            'email' => $input->getArgument('email'),
            'first' => $input->getArgument('name')[0],
            'last' => implode(' ', array_slice($input->getArgument('name'), 1)),
        ];

        $initial = ($input->getArgument('email') && count($input->getArgument('name')) > 1);
        if (!$initial) {
            $this->gatherPerson($input, $output);
        }

        // Verify info
        while (true) {
            if ($this->verifyInput($input, $output)) {
                break;
            }

            $this->gatherPerson($input, $output);
        }
    }

    private function verifyInput(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Confirm that the following information is correct:',
            '',
            'E-mail:    '.$this->inputInfo['email'],
            'Firstname: '.$this->inputInfo['first'],
            'Lastname:  '.$this->inputInfo['last'],
            '',
        ]);

        $helper = $this->getHelper('question');
        while (true) {
            $question = new Question('Is this correct? (y/n): ', 'n');
            $verify = $helper->ask($input, $output, $question);

            if ('y' === $verify) {
                return true;
            } elseif ('n' === $verify) {
                return false;
            }

            $output->writeln("Please enter 'y' or 'n'.");
        }
    }

    private function gatherPerson(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $email = null;
        while (!$email) {
            $question = new Question('Please enter the e-mail address of the person: ');
            $email = $helper->ask($input, $output, $question);
        }

        $firstname = null;
        while (!$firstname) {
            $question = new Question('Please enter the first name of the person: ');
            $firstname = $helper->ask($input, $output, $question);
        }

        $lastname = null;
        while (!$lastname) {
            $question = new Question('Please enter the last name of the person: ');
            $lastname = $helper->ask($input, $output, $question);
        }

        $this->inputInfo = [
            'email' => $email,
            'first' => $firstname,
            'last' => $lastname,
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $firstname = new PersonValue();
        $firstname
            ->setBuiltin('firstname')
            ->setValue($this->inputInfo['first'])
        ;

        $lastname = new PersonValue();
        $lastname
            ->setBuiltin('lastname')
            ->setValue($this->inputInfo['last'])
        ;

        $person = new Person();
        $person
            ->setEmail($this->inputInfo['email'])
            ->setShortnameExpr('firstname')
            ->setNameExpr('firstname~" "~lastname')
            ->addFieldValue($firstname)
            ->addFieldValue($lastname)
        ;

        $this->em->persist($firstname);
        $this->em->persist($lastname);
        $this->em->persist($person);
        $this->em->flush();

        $output->writeln($person->getCanonical().' registered!');
    }
}
