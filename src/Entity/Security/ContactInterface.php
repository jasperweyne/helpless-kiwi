<?php

namespace App\Entity\Security;

/**
 * An interface for contact details.
 */
interface ContactInterface extends \Stringable
{
    public function getName(): ?string;

    public function getEmail(): ?string;

    public function getCanonical(): string;
}
