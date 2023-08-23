<?php

namespace App\Entity\Security;

interface ContactInterface extends \Stringable
{
    public function getName(): ?string;

    public function getEmail(): ?string;

    public function getCanonical(): string;
}
