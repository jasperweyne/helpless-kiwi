<?php

namespace App\Entity\Security;

interface ContactInterface
{
    public function getName(): ?string;

    public function getEmail(): ?string;

    public function getCanonical(): string;
}
