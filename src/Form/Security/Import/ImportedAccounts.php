<?php

namespace App\Form\Security\Import;

use App\Entity\Security\LocalAccount;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ImportedAccounts
{
    /** @var ?Collection<int, LocalAccount> */
    private $added;

    /** @var ?Collection<int, LocalAccount> */
    private $removed;

    /** @var ?Collection<int, LocalAccount> */
    private $updated;

    public function __construct(
        /** @var LocalAccount[] */
        private array $currentAccounts,
        public ?UploadedFile $file = null
    ) {
    }

    /**
     * @return Collection<int, LocalAccount>
     */
    public function getAdded(): Collection
    {
        if ($this->added === null) {
            $this->parseFile();
        }

        assert($this->added !== null);
        return $this->added;
    }

    /**
     * @return Collection<int, LocalAccount>
     */
    public function getRemoved(): Collection
    {
        if ($this->removed === null) {
            $this->parseFile();
        }

        assert($this->removed !== null);
        return $this->removed;
    }

    /**
     * @return Collection<int, LocalAccount>
     */
    public function getUpdated(): Collection
    {
        if ($this->updated === null) {
            $this->parseFile();
        }

        assert($this->updated !== null);
        return $this->updated;
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
        $this->added = new ArrayCollection();
        $this->updated = new ArrayCollection();
        $this->removed = new ArrayCollection($this->currentAccounts); // copy

        // Iterate over rows
        while (is_array($row = $handle->fgetcsv())) {
            // Validate row column count
            if (count($row) !== count($headers)) {
                continue;
            }

            // Find object for current row
            $key = array_key_first($this->removed->filter(fn (LocalAccount $r) => $findByIdentifier($r, $row))->toArray());
            $object = $key === null ? new LocalAccount() : $this->removed[$key];
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
                $this->added->add($object);
            } else {
                $this->removed->remove($key);
                $this->updated->add($object);
            }
        }
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->added === null) {
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
