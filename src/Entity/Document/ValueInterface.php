<?php

namespace App\Entity\Document;

use Doctrine\ORM\Mapping as ORM;

//Read only interface for values
interface ValueInterface
{
    public function getId(): ?string;

    public function getField(): ?FieldInterface;

    public function getDocument(): ?Document;

    public function getValue(): ?string;
   
    public function getBuiltin(): ?string;
    
    public function __toString();
}
