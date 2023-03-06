<?php

namespace App\Entity\Security;

/**
 * An interface for contact details.
 */
interface ContactInterface
{
    public function getName(): ?string;

    public function getEmail(): ?string;

    public function getCanonical(): string;
}
