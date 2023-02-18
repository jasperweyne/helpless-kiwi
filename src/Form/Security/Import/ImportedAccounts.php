<?php

namespace App\Form\Security\Import;

use App\Entity\Security\LocalAccount;
use App\Event\Security\CreateAccountsEvent;
use App\Event\Security\RemoveAccountsEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ImportedAccounts
{
    /** @var ?Collection<int, LocalAccount> */
    private $additions;

    /** @var ?Collection<int, LocalAccount> */
    private $removals;

    /** @var ?Collection<int, LocalAccount> */
    private $updates;

    public bool $willAdd = true;

    public bool $willRemove = false;

    public function __construct(
        /** @var LocalAccount[] */
        private array $currentAccounts,
        public ?UploadedFile $file = null
    ) {
    }

    public function executeImport(EventDispatcherInterface $dispatcher, EntityManagerInterface $em): void
    {
        // Create accounts from additions
        if ($this->willAdd) {
            $accounts = $this->getAdditions()->toArray();
            $dispatcher->dispatch(new CreateAccountsEvent($accounts));
        }

        // Delete accounts from removals
        if ($this->willRemove) {
            $accounts = $this->getRemovals()->toArray();
            $dispatcher->dispatch(new RemoveAccountsEvent($accounts));
        }

        // Updates are scheduled by doctrine automatically
        // Possibly flush scheduled changes to database
        $em->flush();
    }

    /**
     * @return Collection<int, LocalAccount>
     */
    public function getAdditions(): Collection
    {
        if ($this->additions === null) {
            $this->parseFile();
        }

        assert($this->additions !== null);
        return $this->additions;
    }

    /**
     * @return Collection<int, LocalAccount>
     */
    public function getRemovals(): Collection
    {
        if ($this->removals === null) {
            $this->parseFile();
        }

        assert($this->removals !== null);
        return $this->removals;
    }

    /**
     * @return Collection<int, LocalAccount>
     */
    public function getUpdates(): Collection
    {
        if ($this->updates === null) {
            $this->parseFile();
        }

        assert($this->updates !== null);
        return $this->updates;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function parseFile(): void
    {
        // Validate that file has been loaded
        if ($this->file === null) {
            throw new \InvalidArgumentException('No file loaded');
        }

        // Load headers
        $handle = $this->file->openFile();
        if (!\is_array($headers = $handle->fgetcsv())) {
            throw new \InvalidArgumentException('Invalid CSV file');
        }

        // Create loookup table from headers
        $lookup = [];
        foreach (['email', 'given_name', 'family_name', 'admin', 'oidc'] as $field) {
            if (false !== $i = array_search($field, $headers, true)) {
                $lookup[$field] = $i;
            }
        }

        // Create a function that can check whether a CSV row refers to a given LocalAccount object
        $findByIdentifier = match (true) {
            isset($lookup['oidc']) => (fn (LocalAccount $account, array $row) => $account->getOidc() === $row[$lookup['oidc']]),
            isset($lookup['email']) => (fn (LocalAccount $account, array $row) => $account->getEmail() === $row[$lookup['email']]),
            default => throw new \InvalidArgumentException("Headers must contain email or oidc field"),
        };

        // Reset modification arrays
        // It is assumed that all current data is deleted, unless it's present in the CSV
        $this->additions = new ArrayCollection();
        $this->updates = new ArrayCollection();
        $this->removals = new ArrayCollection($this->currentAccounts); // copy

        // Iterate over rows
        while (is_array($row = $handle->fgetcsv())) {
            // Validate row column count
            if (count($row) !== count($headers)) {
                continue;
            }

            // Find object for current row
            $key = array_key_first($this->removals->filter(fn (LocalAccount $r) => $findByIdentifier($r, $row))->toArray());
            $object = $key === null ? new LocalAccount() : $this->removals[$key];
            assert($object !== null);

            // Update contents of object
            if (isset($lookup['oidc'])) {
                $object->setOidc($row[$lookup['oidc']]);
            }
            if (isset($lookup['email'])) {
                $object->setEmail($row[$lookup['email']]);
            }
            if (isset($lookup['given_name'])) {
                $object->setGivenName($row[$lookup['given_name']]);
            }
            if (isset($lookup['family_name'])) {
                $object->setFamilyName($row[$lookup['family_name']]);
            }
            if (isset($lookup['admin'])) {
                $object->setRoles(filter_var($row[$lookup['admin']], FILTER_VALIDATE_BOOLEAN) ? ['ROLE_ADMIN'] : []);
            }

            // Update the collections
            if ($key === null) {
                $this->additions->add($object);
            } else {
                $this->removals->remove($key);
                $this->updates->add($object);
            }
        }
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->additions === null) {
            try {
                $this->parseFile();
            } catch (\Exception $e) {
                $context->buildViolation($e->getMessage())
                    ->atPath('file')
                    ->addViolation();
            }
        }
    }
}
