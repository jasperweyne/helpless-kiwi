<?php

namespace App\Command;

use App\Entity\Document\Document;
use App\Entity\Document\Value;
use App\Entity\Document\Scheme;
use App\Entity\Document\Field;
use App\Entity\Document\Index;
use App\Entity\Person\Person;

use App\Entity\Person\PersonValue;
use App\Entity\Person\PersonField;
use App\Entity\Person\PersonScheme;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


class CreateDocumentCommand extends Command
{
    private $em;
    private $inputInfo;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create-document2';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new document.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a document...')

            // possible arguments
            ->addArgument('setting', InputArgument::REQUIRED, 'all or case')

            ->addArgument('email', InputArgument::IS_ARRAY, 'The e-mail addresses of the users.')

            
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $set = $input->getArgument('setting');
        $emails = $input->getArgument('email');

        if ($set == "all") {
            $persons = $this->em->getRepository(Person::class)->findAll();

            foreach ($persons as $person){
                if ($person->getDocument()==null) {
                    $this->generateDocument($person);
                } else {
                    $output->writeln('Person already has a document class.');
                }
                
            }

        } else {
            $person = $this->em->getRepository(Person::class)->findOneBy(['email' => $emails[0]]);
            if ($person->getDocument()==null) {
                $this->generateDocument($person);
            } else {
                $output->writeln('Person already has a document class.');
            }
        }

        $output->writeln('Succes hopefully');
    }

    protected function generateDocument(Person $person) {
        $oldScheme = $person->getOldScheme();
        $oldFields = null;
        $oldValues = $person->getOldFieldValues();
        $oldKeyVals = null;

        //Old scheme exists at all. Else create emtpy scheme if not already existing?
        $newScheme = null;
        if ($oldScheme) {
            $oldFields = $oldScheme->getFields();
            $oldKeyVals = $this->getOldKeyValue($oldScheme,$oldValues);

            //creating new scheme if not found already in the current schemes. 
            $newScheme = $this->em->getRepository(Scheme::class)->findOneBy(['name' => $oldScheme->getName()]);
            if ($newScheme == null) {
                $newScheme = new Scheme();
                $newScheme->setName($oldScheme->getName());

                foreach ($oldFields as $oldField) {
                    $newField = new Field();
                    $newField->setName($oldField->getName());
                    $newField->setSlug($oldField->getSlug());
                    $newField->setValueType($oldField->getValueType());
                    
                    //Optional for now
                    //$newField->setUserEditOnly($oldField->getUserEditOnly());
                    $newField->setScheme($newScheme);
                    $this->em->persist($newField);
                }

                //Indexes but not yet done.
                //$shortname = new Index();
                //$longname = new Index();

                $this->em->persist($newScheme);
            }
            $this->em->flush();
        }
        
        //Generate the document and copy the values of all fields. 
        $document = new Document();
        $document->setScheme($newScheme);

        if ($oldKeyVals) {
            //Only does something if we actually have a fields. 
            foreach ($oldKeyVals as $oldKeyVal) {
                $newValue = new Value();
                $oldField = $oldKeyVal->get('key');
                $oldValue = $oldKeyVal->get('value');
                $newField = $newScheme->getField($oldField->getName());

                $newValue->setField($newField);
                $newValue->setDocument($document);
                $newValue->setBuiltin($oldValue->getBuiltin());
                $newValue->setValue($oldValue->getValue());
                $this->em->persist($newValue);
            }
        }
        

        $this->em->persist($document);
        
        //Set the document to the person
        $person->setDocument($document);
        $this->em->persist($person);

        $this->em->flush();


    }

    protected function getOldKeyValue($oldScheme,$oldValues): Collection {
        $oldKeyVals = new ArrayCollection();

        if ($oldScheme) {
        
            foreach ($oldScheme->getFields() as $field) {
                $keyVals[] = [
                    'key' => $field,
                    'value' => $this->getValue($field,$oldValues),
                ];
            }
        } else {
            foreach ($oldValues as $value) {
                $keyVals[] = [
                    'key' => $value->getBuiltin() ?? $value->getField(),
                    'value' => $value,
                ];
            }
        }

        return $oldKeyVals;
    }

    protected function getField($oldFields, $name): ?PersonField {
        foreach ($oldFields as $field) {
            if ($field->getName() == $name ) {
               return $field;
            } 
        }

        return null;
    }


    protected function getValue($field,$values): ?PersonValue
    {
        foreach ($values as $value) {
            if ($field instanceof PersonField) {
                $valueField = $value->getField();
                if (!is_null($valueField) && $valueField->getId() == $field->getId()) {
                    return $value;
                }
            } else {
                if ($value->getBuiltin() == $field) {
                    return $value;
                }
            }
        }

        return null;
    }


}
